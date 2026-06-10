<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    /**
     * Stock Adjustment (Opname/Rusak/Hilang)
     */
    public function adjustment(Request $request)
    {
        try {

            $validated = $request->validate([
                'stock_id' => 'required|exists:stocks,id',
                'quantity_after' => 'required|integer|min:0',
                'type' => 'required|in:penambahan,pengurangan,koreksi',
                'reason' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            $stock = Stock::findOrFail(
                $validated['stock_id']
            );

            $quantity_before =
                $stock->quantity;

            $adjustment_qty =
                $validated['quantity_after']
                - $quantity_before;

            // Create adjustment record
            $adjustment =
                StockAdjustment::create([

                    'stock_id' =>
                        $stock->id,

                    'quantity_before' =>
                        $quantity_before,

                    'quantity_after' =>
                        $validated['quantity_after'],

                    'adjustment_qty' =>
                        $adjustment_qty,

                    'type' =>
                        $validated['type'],

                    'reason' =>
                        $validated['reason'],

                    'notes' =>
                        $validated['notes']
                        ?? null,

                    'adjusted_by' =>
                        Auth::id(),
                ]);

            // Update stock
            $stock->update([
                'quantity' =>
                    $validated['quantity_after']
            ]);

            return response()->json([
                'message' =>
                    'Stock adjustment recorded successfully',

                'data' =>
                    $adjustment->load(
                        'stock.medicine',
                        'adjustedBy'
                    )
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                    $e->getMessage()
            ], 400);
        }
    }

    /**
     * Stock Opname (Fisik Inventory)
     */
    public function opname(Request $request)
    {
        try {

            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'adjustments' => 'required|array|min:1',
                'adjustments.*.stock_id' => 'required|exists:stocks,id',
                'adjustments.*.quantity_after' => 'required|integer|min:0',
            ]);

            $adjustments = [];
            $stockOpnames = [];

            foreach ($validated['adjustments'] as $adj) {

                $stock = Stock::findOrFail(
                    $adj['stock_id']
                );

                $quantity_before =
                    $stock->quantity;

                $quantity_after =
                    $adj['quantity_after'];

                $difference =
                    $quantity_after -
                    $quantity_before;

                // VALIDASI:
                // kalau tidak ada perubahan skip
                if ($difference == 0) {
                    continue;
                }

                // simpan ke stock_opname
                $opname = \App\Models\StockOpname::create([
                    'reference_number' =>
                        'OPN-' . now()->format('YmdHis'),

                    'warehouse_id' =>
                        $validated['warehouse_id'],

                    'medicine_id' =>
                        $stock->medicine_id,

                    'system_quantity' =>
                        $quantity_before,

                    'physical_quantity' =>
                        $quantity_after,

                    'difference' =>
                        $difference,

                    'status' =>
                        'approved',

                    'created_by' =>
                        Auth::id(),

                    'approved_by' =>
                        Auth::id(),

                    'approved_at' =>
                        now(),

                    'notes' =>
                        'Stock opname system'
                ]);

                $stockOpnames[] = $opname;

                // simpan adjustment
                $adjustment =
                    StockAdjustment::create([

                        'stock_id' =>
                            $stock->id,

                        'quantity_before' =>
                            $quantity_before,

                        'quantity_after' =>
                            $quantity_after,

                        'adjustment_qty' =>
                            $difference,

                        'type' =>
                            'koreksi',

                        'reason' =>
                            'Stock opname',

                        'adjusted_by' =>
                            Auth::id(),
                    ]);

                // update stock
                $stock->update([
                    'quantity' =>
                        $quantity_after
                ]);

                $adjustments[] =
                    $adjustment;
            }

            // kalau tidak ada perubahan
            if (count($adjustments) == 0) {

                return response()->json([
                    'message' =>
                        'Tidak ada perubahan stok'
                ], 422);
            }

            return response()->json([
                'message' =>
                    'Stock opname berhasil',

                'adjustments' =>
                    $adjustments,

                'stock_opname' =>
                    $stockOpnames

            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                    $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Low Stock Items
     */
    public function lowStock(Request $request)
    {
        try {

            $stocks = Stock::lowStock()
                ->with([
                    'medicine',
                    'warehouse'
                ])
                ->paginate(20);

            return response()->json([
                'message' =>
                    'Low stock items retrieved',

                'data' =>
                    $stocks
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                    $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Expired Items
     */
    public function expired(Request $request)
    {
        try {

            $stocks = Stock::expired()
                ->with([
                    'medicine',
                    'warehouse'
                ])
                ->paginate(20);

            return response()->json([
                'message' =>
                    'Expired items retrieved',

                'data' =>
                    $stocks
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                    $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Expiring Soon Items (30 days)
     */
    public function expiringSoon(Request $request)
    {
        try {

            $stocks =
                Stock::expiringSoon()
                ->with([
                    'medicine',
                    'warehouse'
                ])
                ->orderBy(
                    'expired_date',
                    'asc'
                )
                ->paginate(20);

            return response()->json([
                'message' =>
                    'Items expiring soon retrieved',

                'data' =>
                    $stocks
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                    $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all stocks
     */
    public function index(Request $request)
    {
        try {

            $query = Stock::with([
                'medicine',
                'warehouse',
                'shelf'
            ]);

            // filter warehouse
            if ($request->warehouse_id) {
                $query->where(
                    'warehouse_id',
                    $request->warehouse_id
                );
            }

            // search medicine
            if ($request->search) {
                $query->whereHas(
                    'medicine',
                    function ($q) use ($request) {
                        $q->where(
                            'name',
                            'like',
                            '%' . $request->search . '%'
                        )
                        ->orWhere(
                            'code',
                            'like',
                            '%' . $request->search . '%'
                        );
                    }
                );
            }

            $stocks = $query
                ->latest()
                ->paginate(
                    $request->per_page ?? 20
                );

            return response()->json([
                'message' => 'Stocks retrieved successfully',
                'data' => $stocks
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock detail
     */
    public function show($id)
    {
        try {

            $stock = Stock::with([
                'medicine',
                'warehouse',
                'shelf'
            ])->findOrFail($id);

            return response()->json([
                'message' => 'Stock detail retrieved',
                'data' => $stock
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }


  /**
     * Store a newly created stock in storage.
     */
    public function store(Request $request)
    {
        try {
            // 1. Normalisasi input Qty agar fleksibel (bisa qty / quantity)
            $quantityInput = $request->input('qty') ?? $request->input('quantity');
            
            // 2. Normalisasi tanggal expired (bisa expiration_date / expired_date)
            $expiredInput = $request->input('expiration_date') ?? $request->input('expired_date');

            // Inject kembali ke request agar terbaca mulus oleh validator
            $request->merge([
                'qty' => $quantityInput,
                'expiration_date' => $expiredInput
            ]);

            // 3. Jalankan Validasi
            $validated = $request->validate([
                'medicine_id'     => 'required|exists:medicines,id',
                'warehouse_id'    => 'required|exists:warehouses,id',
                'qty'             => 'required|integer|min:1',
                'expiration_date' => 'nullable|date',
            ]);

            // 4. Create record dengan fallback aman jika 'expiration_date' tidak lolos / bernilai null
            $stock = Stock::create([
                'medicine_id'  => $validated['medicine_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'quantity'     => $validated['qty'],
                'expired_date' => $validated['expiration_date'] ?? null, // Menggunakan operator ?? agar aman dari undefined key
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stok baru berhasil ditambahkan!',
                'data'    => $stock->load('medicine', 'warehouse')
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $v) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $v->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}