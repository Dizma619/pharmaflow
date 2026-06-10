<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\Cashflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Get all purchases
     */
    public function index(Request $request)
    {
        try {
            $query = Purchase::with([
                'supplier',
                'items.medicine'
            ]);

            // Filter supplier
            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            // Filter status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter date range
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            // Menggunakan get() agar langsung mengembalikan Array Data ke Frontend
            $purchases = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'message' => 'Purchases retrieved successfully',
                'data' => $purchases
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create purchase
     */
  
        public function store(Request $request)
{
    dd('STORE BARU');
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'items' => 'required|array|min:1',
                'items.*.medicine_id' => 'required|exists:medicines,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.expired_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $totalAmount = 0;

            // Create purchase
          $purchase = Purchase::create([
    'po_number' => 'PO-' . now()->format('YmdHis'),
    'supplier_id' => $validated['supplier_id'],
    'subtotal' => 0,
    'tax_amount' => 0,
    'shipping_cost' => 0,
    'total_amount' => 0,
    'status' => 'draft',
    'purchase_date' => now(),
    'notes' => $validated['notes'] ?? null,
    'items_total' => count($validated['items']),
]);

            // Create items
            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $item['medicine_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                    'expired_date' => $item['expired_date'] ?? null,
                    'quantity_received' => 0,
                ]);
            }

            // Update total
            $purchase->update([
                'total_amount' => $totalAmount
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Purchase created successfully',
                'data' => $purchase->load(['supplier', 'items.medicine'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show purchase detail
     */
    public function show($id)
    {
        try {
            $purchase = Purchase::with([
                'supplier',
                'items.medicine',
                'receivedByUser'
            ])->findOrFail($id);

            return response()->json([
                'message' => 'Purchase retrieved successfully',
                'data' => $purchase
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Purchase not found'
            ], 404);
        }
    }

    /**
     * Update purchase
     */
    public function update(Request $request, $id)
    {
        try {
            $purchase = Purchase::findOrFail($id);

            // Only pending can edit
           if ($purchase->status !== 'draft') {
                return response()->json([
                    'message' => 'Can only edit pending purchases'
                ], 422);
            }

            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'items' => 'required|array|min:1',
                'items.*.medicine_id' => 'required|exists:medicines,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.expired_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Delete old items
            $purchase->items()->delete();

            $totalAmount = 0;

            // Insert new items
            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $item['medicine_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                    'expired_date' => $item['expired_date'] ?? null,
                    'quantity_received' => 0,
                ]);
            }

            // Update purchase
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'items_total' => count($validated['items']),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Purchase updated successfully',
                'data' => $purchase->load(['supplier', 'items.medicine'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Receive Purchase
     * Input stock + create cashflow
     */
    public function receive(Request $request, $id)
    {
        try {
            $purchase = Purchase::with([
                'items',
                'supplier'
            ])->findOrFail($id);

            // Already received
            if ($purchase->status === 'received') {
                return response()->json([
                    'message' => 'Purchase already received'
                ], 422);
            }

            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'shelf_id' => 'nullable|exists:shelves,id',
                'received_items' => 'required|array',
                'received_items.*.purchase_item_id' => 'required|exists:purchase_items,id',
                'received_items.*.quantity_received' => 'required|integer|min:0',
            ]);

            DB::beginTransaction();

            $totalReceived = 0;

            foreach ($validated['received_items'] as $item) {
                $purchaseItem = PurchaseItem::findOrFail($item['purchase_item_id']);

                // Update received qty
                $purchaseItem->update([
                    'quantity_received' => $item['quantity_received']
                ]);

                // Input stock
                if ($item['quantity_received'] > 0) {
                    $stock = Stock::firstOrCreate(
                        [
                            'medicine_id' => $purchaseItem->medicine_id,
                            'warehouse_id' => $validated['warehouse_id'],
                            'expired_date' => $purchaseItem->expired_date,
                        ],
                        [
                            'quantity' => 0,
                            'shelf_id' => $validated['shelf_id'] ?? null,
                        ]
                    );

                    $stock->increment('quantity', $item['quantity_received']);
                    $totalReceived += $item['quantity_received'];
                }
            }

            // Update purchase
            $purchase->update([
                'status' => 'received',
                'items_received' => $totalReceived,
                'received_at' => now(),
                'received_by' => Auth::id(),
            ]);

            // Create cashflow
            $this->recordPurchaseCashflow($purchase);

            DB::commit();

            return response()->json([
                'message' => 'Purchase received successfully',
                'data' => $purchase->load('items.medicine')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete purchase
     */
    public function destroy($id)
    {
        try {
            $purchase = Purchase::findOrFail($id);

            if ($purchase->status !== 'draft') {
                return response()->json([
                    'message' => 'Can only delete pending purchases'
                ], 422);
            }

            $purchase->delete();

            return response()->json([
                'message' => 'Purchase deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Record purchase cashflow
     */
    private function recordPurchaseCashflow($purchase)
    {
        // Prevent duplicate cashflow
        $exists = Cashflow::where('reference_type', 'Purchase')
            ->where('reference_id', $purchase->id)
            ->exists();

        if ($exists) {
            return;
        }

        Cashflow::create([
            'transaction_date' => $purchase->received_at ?? now(),
            'type' => 'pengeluaran',
            'category' => 'pembelian_obat',
            'amount' => $purchase->total_amount,
            'description' => "Pembelian dari {$purchase->supplier->name}",
            'reference_type' => 'Purchase',
            'reference_id' => $purchase->id,
            'notes' =>  "Purchase #: {$purchase->po_number}",
        ]);
    }
}