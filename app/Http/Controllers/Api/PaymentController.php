<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cashflow;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Setup Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create Snap Token untuk payment
     * PUBLIC - support guest & auth
     */
    public function createSnapToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $order = Order::findOrFail($validated['order_id']);

            // Para Snap Token
            $snapParams = [
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => (int) $order->final_amount,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name,
                    'phone' => $order->phone,
                    'email' => $order->user?->email ?? 'guest@farmaflow.local',
                    'address' => $order->getFullAddress(),
                ],
                'items_details' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->medicine_id,
                        'price' => (int) $item->unit_price,
                        'quantity' => $item->quantity,
                        'name' => $item->medicine->name,
                    ];
                })->toArray(),
                'callbacks' => [
                    'finish' => config('app.url') . '/order-success',
                    'error' => config('app.url') . '/order-failed',
                    'pending' => config('app.url') . '/order-pending',
                ],
            ];

            $snapToken = Snap::getSnapToken($snapParams);

            // Save payment record
            Payment::create([
                'order_id' => $order->id,
                'amount' => $order->final_amount,
                'payment_method' => 'midtrans',
                'status' => 'pending',
                'transaction_id' => $order->order_number,
                'payment_url' => 'https://app.midtrans.com/snap/v2/vtweb/' . $snapToken,
            ]);

            return response()->json([
                'message' => 'Snap token created',
                'snap_token' => $snapToken,
                'redirect_url' => 'https://app.midtrans.com/snap/v2/vtweb/' . $snapToken,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Payment status check
     * PUBLIC - support guest & auth
     */
    public function status(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $payment = Payment::where('order_id', $id)->first();

            if (!$payment) {
                return response()->json([
                    'message' => 'Payment not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'message' => 'Payment status retrieved',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount' => $order->final_amount,
                    'status' => $payment->status,
                    'payment_method' => $payment->payment_method,
                    'order_status' => $order->status,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }
    }

    /**
     * Confirm COD payment
     * PUBLIC - support guest & auth
     */
    public function confirmCOD(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $order = Order::findOrFail($validated['order_id']);

            if ($order->payment_method !== 'cod') {
                return response()->json([
                    'message' => 'Order payment method is not COD'
                ], 422);
            }

            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'amount' => $order->final_amount,
                'payment_method' => 'cod',
                'status' => 'pending',
                'transaction_id' => 'COD-' . $order->order_number,
            ]);

            // Update order status
            $order->update([
                'payment_status' => 'pending',
                'status' => 'diproses', // Langsung diproses untuk COD
            ]);

            return response()->json([
                'message' => 'COD order confirmed',
                'data' => $order
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

     public function notification(Request $request)
    {
        try {
            $json = json_decode($request->getContent());

            $transactionStatus = $json->transaction_status;
            $orderId = $json->order_id;
            $fraudStatus = $json->fraud_status;

            // Find order
            $order = Order::where('order_number', $orderId)->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Update payment status
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $order->update(['payment_status' => 'pending']);
                } else if ($fraudStatus == 'accept') {
                    $order->update([
                        'payment_status' => 'completed',
                        'status' => 'diproses',
                    ]);
                    $this->recordCashflow($order, 'completed');
                    $this->reduceStock($order);
                }
            } else if ($transactionStatus == 'settlement') {
                $order->update([
                    'payment_status' => 'completed',
                    'status' => 'diproses',
                ]);
                $this->recordCashflow($order, 'completed');
                $this->reduceStock($order);
            } else if ($transactionStatus == 'pending') {
                $order->update(['payment_status' => 'pending']);
            } else if ($transactionStatus == 'deny') {
                $order->update(['payment_status' => 'failed']);
                $this->recordCashflow($order, 'failed');
            } else if ($transactionStatus == 'expire') {
                $order->update(['payment_status' => 'expired']);
            } else if ($transactionStatus == 'cancel') {
                $order->update(['payment_status' => 'cancelled']);
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Helper: Record cashflow when payment completed
     */
    private function recordCashflow($order, $status)
    {
        if ($status === 'completed') {
            // Create income cashflow
            Cashflow::create([
                'transaction_date' => now(),
                'type' => 'income',
                'category' => 'penjualan_online',
                'amount' => $order->total_amount,
                'description' => "Penjualan Order #{$order->order_number}",
                'reference_type' => 'Order',
                'reference_id' => $order->id,
                'notes' => "Payment via {$order->payment_method}",
            ]);
        } else if ($status === 'failed') {
            // Optionally record failed transaction
            Cashflow::create([
                'transaction_date' => now(),
                'type' => 'expense',
                'category' => 'failed_transaction',
                'amount' => 0,
                'description' => "Failed payment Order #{$order->order_number}",
                'reference_type' => 'Order',
                'reference_id' => $order->id,
                'notes' => 'Payment failed - no money in/out',
            ]);
        }
    }

    /**
     * Helper: Reduce stock when payment confirmed
     */
    private function reduceStock($order)
    {
        foreach ($order->items as $item) {
            \App\Models\Stock::where('medicine_id', $item->medicine_id)
                           ->decrement('quantity', $item->quantity);
        }
    }
}