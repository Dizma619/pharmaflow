<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\Cashflow;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Events\NewTransactionNotification;

class TransactionController extends Controller
{
    /**
     * LIST TRANSACTION
     */
    public function index(Request $request)
    {
        $query = Transaction::with([
            'kasir',
            'items.medicine'
        ]);

        if ($request->filled('payment_status')) {
            $query->where(
                'payment_status',
                $request->payment_status
            );
        }

        if ($request->filled('today')) {
            $query->whereDate(
                'created_at',
                today()
            );
        }

        $transactions = $query
            ->latest()
            ->paginate(
                $request->per_page ?? 20
            );

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * STORE POS TRANSACTION
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            /**
             * VALIDATION
             */
            $validated = $request->validate([

                'items' =>
                'required|array|min:1',

                'items.*.medicine_id' =>
                'required|exists:medicines,id',

                'items.*.quantity' =>
                'required|integer|min:1',

                'discount_amount' =>
                'nullable|numeric|min:0',

                'payment_method' =>
                'required|in:cash,transfer,credit_card',

                'cash_received' =>
                'nullable|numeric|min:0',

                'notes' =>
                'nullable|string|max:500'
            ]);

            $subtotal = 0;
            $transactionItems = [];

            /**
             * VALIDASI STOK
             */
            foreach (
                $validated['items']
                as $item
            ) {

                $medicine =
                    Medicine::findOrFail(
                        $item['medicine_id']
                    );

                $availableStock =
                    Stock::where(
                        'medicine_id',
                        $medicine->id
                    )
                    ->lockForUpdate()
                    ->sum('quantity');

                if (
                    $availableStock <
                    $item['quantity']
                ) {

                    throw new \Exception(
                        "Stok {$medicine->name} tidak cukup"
                    );
                }

                $itemSubtotal =
                    $medicine->selling_price
                    *
                    $item['quantity'];

                $subtotal +=
                    $itemSubtotal;

                $transactionItems[] = [

                    'medicine_id' =>
                    $medicine->id,

                    'medicine_name' =>
                    $medicine->name,

                    'quantity' =>
                    $item['quantity'],

                    'unit_price' =>
                    $medicine->selling_price,

                    'subtotal' =>
                    $itemSubtotal
                ];
            }

            /**
             * DISCOUNT
             */
            $discount =
                $validated['discount_amount'] ?? 0;

            $grandTotal =
                $subtotal - $discount;

            /**
             * PAYMENT
             */
            $cashReceived =
                $validated['cash_received'] ?? 0;

            $change = 0;

            if (
                $validated['payment_method'] === 'cash'
            ) {

                if (
                    $cashReceived <
                    $grandTotal
                ) {

                    throw new \Exception(
                        'Uang pembayaran kurang'
                    );
                }

                $change =
                    $cashReceived -
                    $grandTotal;
            }

            /**
             * CREATE TRANSACTION
             */
            $transaction =
                Transaction::create([

                    'kasir_id' =>
                    Auth::id(),

                    'total_amount' =>
                    $subtotal,

                    'discount_amount' =>
                    $discount,

                    'final_amount' =>
                    $grandTotal,

                    'cash_received' =>
                    $cashReceived,

                    'change_amount' =>
                    $change,

                    'payment_method' =>
                    $validated['payment_method'],

                    'payment_status' =>
                    'lunas',

                    'notes' =>
                    $validated['notes'] ?? null
                ]);

            /**
             * SAVE ITEMS + FIFO STOCK
             * + LOW STOCK WARNING
             */
            foreach (
                $transactionItems
                as $item
            ) {

                TransactionItem::create([

                    'transaction_id' =>
                    $transaction->id,

                    'medicine_id' =>
                    $item['medicine_id'],

                    'quantity' =>
                    $item['quantity'],

                    'unit_price' =>
                    $item['unit_price'],

                    'subtotal' =>
                    $item['subtotal']
                ]);

                /**
                 * FIFO STOCK REDUCTION
                 */
                $stocks =
                    Stock::where(
                        'medicine_id',
                        $item['medicine_id']
                    )
                    ->available()
                    ->FIFO()
                    ->lockForUpdate()
                    ->get();

                $qtyNeed =
                    $item['quantity'];

                foreach (
                    $stocks
                    as $stock
                ) {

                    if (
                        $qtyNeed <= 0
                    ) {
                        break;
                    }

                    if (
                        $stock->quantity >=
                        $qtyNeed
                    ) {

                        $stock->decrement(
                            'quantity',
                            $qtyNeed
                        );

                        $qtyNeed = 0;
                    } else {

                        $qtyNeed -=
                            $stock->quantity;

                        $stock->update([
                            'quantity' => 0
                        ]);
                    }
                }

                /**
                 * LOW STOCK WARNING
                 */
                $medicine =
                    Medicine::find(
                        $item['medicine_id']
                    );

                $remainingStock =
                    Stock::where(
                        'medicine_id',
                        $item['medicine_id']
                    )->sum('quantity');

                if (
                    $remainingStock <=
                    $medicine->stock_minimum
                ) {

                    $receivers =
                        User::whereIn(
                            'role',
                            ['owner', 'staff']
                        )->get();

                    foreach (
                        $receivers
                        as $receiver
                    ) {

                        Notification::create([

                            'user_id' =>
                            $receiver->id,

                            'title' =>
                            'Stok Menipis',

                            'message' =>
                            'Obat ' .
                                $medicine->name .
                                ' hampir habis. Sisa stok: ' .
                                $remainingStock,

                            'type' =>
                            'stock_warning',

                            'is_read' =>
                            false
                        ]);
                    }
                }
            }
            /**
             * CASHFLOW AUTO
             */
            Cashflow::create([

                'transaction_date' =>
                now(),

                'type' =>
                'income',

                'category' =>
                'penjualan',

                'amount' =>
                $grandTotal,

                'description' =>
                'Penjualan POS - ' .
                    $transaction->reference_number,

                'reference_type' =>
                'transaction',

                'reference_id' =>
                $transaction->id,

                'notes' =>
                'Staff: ' .
                    Auth::user()->name
            ]);

            /**
             * NOTIFICATION
             * OWNER + STAFF
             */
            $receivers =
                User::whereIn(
                    'role',
                    ['owner', 'staff']
                )->get();

            foreach (
                $receivers
                as $receiver
            ) {

                $notification =
                    Notification::create([

                        'user_id' =>
                        $receiver->id,

                        'title' =>
                        'Transaksi Baru',

                        'message' =>
                        'Transaksi ' .
                            $transaction
                            ->reference_number .
                            ' berhasil dibuat. Total Rp ' .
                            number_format(
                                $grandTotal,
                                0,
                                ',',
                                '.'
                            ),

                        'type' =>
                        'transaction',

                        'is_read' =>
                        false
                    ]);

                /**
                 * REALTIME BROADCAST
                 */
                broadcast(
                    new NewTransactionNotification(
                        $notification
                    )
                );
            }
            DB::commit();

            return response()->json([

                'success' => true,

                'message' =>
                'Transaksi berhasil',

                'data' => [

                    'transaction_id' =>
                    $transaction->id,

                    'reference_number' =>
                    $transaction
                        ->reference_number,

                    'grand_total' =>
                    $transaction
                        ->final_amount,

                    'cash_received' =>
                    $transaction
                        ->cash_received,

                    'change_amount' =>
                    $transaction
                        ->change_amount,

                    'payment_method' =>
                    $transaction
                        ->payment_method
                ]
            ], 201);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 400);
        }
    }

    /**
     * REALTIME CHANGE CALCULATOR
     */
    public function calculateChange(
        Request $request
    ) {
        try {

            $validated =
                $request->validate([

                    'items' =>
                    'required|array|min:1',

                    'items.*.medicine_id' =>
                    'required|exists:medicines,id',

                    'items.*.quantity' =>
                    'required|integer|min:1',

                    'discount_amount' =>
                    'nullable|numeric|min:0',

                    'cash_received' =>
                    'required|numeric|min:0'
                ]);

            $subtotal = 0;

            foreach (
                $validated['items']
                as $item
            ) {

                $medicine =
                    Medicine::findOrFail(
                        $item['medicine_id']
                    );

                $subtotal +=
                    $medicine->selling_price
                    *
                    $item['quantity'];
            }

            $discount =
                $validated['discount_amount'] ?? 0;

            $grandTotal =
                $subtotal -
                $discount;

            $cashReceived =
                $validated['cash_received'];

            $change =
                max(
                    0,
                    $cashReceived -
                        $grandTotal
                );

            $isEnough =
                $cashReceived
                >=
                $grandTotal;

            return response()->json([

                'success' => true,

                'data' => [

                    'subtotal' =>
                    $subtotal,

                    'discount' =>
                    $discount,

                    'grand_total' =>
                    $grandTotal,

                    'cash_received' =>
                    $cashReceived,

                    'change_amount' =>
                    $change,

                    'payment_valid' =>
                    $isEnough,

                    'shortage' =>
                    $isEnough
                        ? 0
                        : (
                            $grandTotal
                            -
                            $cashReceived
                        )
                ]
            ]);
        } catch (\Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 400);
        }
    }

    /**
     * DETAIL TRANSACTION
     */
    public function show($id)
    {
        $transaction =
            Transaction::with([
                'kasir',
                'items.medicine'
            ])
            ->findOrFail($id);

        return response()->json([

            'success' => true,

            'data' =>
            $transaction
        ]);
    }
}
