<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MedicineController extends Controller
{
    /**
     * Display medicines (with filters)
     */
    public function index(Request $request)
    {
        try {
            $query = Medicine::with(['category', 'supplier', 'stocks']);

            // Search by name or code
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%$search%")
                      ->orWhere('code', 'like', "%$search%");
            }

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->get('category_id'));
            }

            // Filter by supplier
            if ($request->has('supplier_id')) {
                $query->where('supplier_id', $request->get('supplier_id'));
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Filter low stock
            if ($request->boolean('low_stock')) {
                $query->lowStock();
            }

            // Filter expired
            if ($request->boolean('expired')) {
                $query->expired();
            }

            // Order
            $orderBy = $request->get('order_by', 'name');
            $orderDir = $request->get('order_dir', 'asc');
            $query->orderBy($orderBy, $orderDir);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $medicines = $query->paginate($perPage);

            // Add total stock to each medicine
            $medicines->each(function ($medicine) {
                $medicine->total_stock = $medicine->stocks->sum('quantity');
                $medicine->is_low_stock = $medicine->total_stock <= $medicine->stock_minimum;
            });

            return response()->json([
                'message' => 'Data obat berhasil diambil',
                'data' => $medicines
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created medicine
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'base_price' => 'required|numeric|min:0',
                'markup_percentage' => 'required|numeric|min:0|max:100',
                'unit' => 'nullable|string',
                'stock_minimum' => 'nullable|integer|min:0',
                'stock_maximum' => 'nullable|integer|min:0',
                'expired_date' => 'nullable|date',
            ]);

            $medicine = Medicine::create([
                'code' => 'MED-' . strtoupper(uniqid()),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'],
                'supplier_id' => $validated['supplier_id'],
                'base_price' => $validated['base_price'],
                'markup_percentage' => $validated['markup_percentage'],
                'unit' => $validated['unit'] ?? 'pcs',
                'stock_minimum' => $validated['stock_minimum'] ?? 10,
                'stock_maximum' => $validated['stock_maximum'] ?? 100,
                'expired_date' => $validated['expired_date'] ?? null,
                'status' => 'aktif',
            ]);

            return response()->json([
                'message' => 'Obat berhasil dibuat',
                'data' => $medicine->load('category', 'supplier')
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified medicine
     */
    public function show($id)
    {
        try {
            $medicine = Medicine::with(['category', 'supplier', 'stocks.warehouse', 'stocks.shelf'])
                               ->findOrFail($id);

            $medicine->total_stock = $medicine->stocks->sum('quantity');
            $medicine->is_low_stock = $medicine->total_stock <= $medicine->stock_minimum;

            return response()->json([
                'message' => 'Data obat berhasil diambil',
                'data' => $medicine
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Obat tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified medicine
     */
    public function update(Request $request, $id)
    {
        try {
            $medicine = Medicine::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'base_price' => 'required|numeric|min:0',
                'markup_percentage' => 'required|numeric|min:0|max:100',
                'unit' => 'nullable|string',
                'stock_minimum' => 'nullable|integer|min:0',
                'stock_maximum' => 'nullable|integer|min:0',
                'expired_date' => 'nullable|date',
                'status' => 'nullable|in:aktif,tidak_aktif,expired',
            ]);

            $medicine->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? $medicine->description,
                'category_id' => $validated['category_id'],
                'supplier_id' => $validated['supplier_id'],
                'base_price' => $validated['base_price'],
                'markup_percentage' => $validated['markup_percentage'],
                'unit' => $validated['unit'] ?? $medicine->unit,
                'stock_minimum' => $validated['stock_minimum'] ?? $medicine->stock_minimum,
                'stock_maximum' => $validated['stock_maximum'] ?? $medicine->stock_maximum,
                'expired_date' => $validated['expired_date'] ?? $medicine->expired_date,
                'status' => $validated['status'] ?? $medicine->status,
            ]);

            return response()->json([
                'message' => 'Obat berhasil diperbarui',
                'data' => $medicine->load('category', 'supplier')
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Obat tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Remove the specified medicine
     */
    public function destroy($id)
    {
        try {
            $medicine = Medicine::findOrFail($id);
            $medicine->delete();

            return response()->json([
                'message' => 'Obat berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Obat tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Toggle medicine status
     */
    public function toggleStatus($id)
    {
        try {
            $medicine = Medicine::findOrFail($id);

            $newStatus = $medicine->status === 'aktif' ? 'tidak_aktif' : 'aktif';
            $medicine->update(['status' => $newStatus]);

            return response()->json([
                'message' => 'Status obat berhasil diubah',
                'data' => $medicine
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Obat tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Search medicine by code (untuk POS)
     */
    public function searchByCode($code)
    {
        try {
            $medicine = Medicine::where('code', $code)
                              ->orWhere('code', 'like', "%$code%")
                              ->with(['stocks.warehouse', 'category'])
                              ->first();

            if (!$medicine) {
                return response()->json([
                    'message' => 'Obat tidak ditemukan'
                ], 404);
            }

            $medicine->total_stock = $medicine->stocks->sum('quantity');

            return response()->json([
                'message' => 'Data obat berhasil diambil',
                'data' => $medicine
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}