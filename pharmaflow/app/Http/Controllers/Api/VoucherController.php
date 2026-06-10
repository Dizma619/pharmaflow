<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    /**
     * Get all vouchers
     */
    public function index(Request $request)
    {
        try {
            $query = Voucher::query();

            // Get only active
            if (!$request->has('all')) {
                $query->active();
            }

            // Filter by status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Search
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('code', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            }

            $vouchers = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'message' => 'Data voucher berhasil diambil',
                'data' => $vouchers
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create voucher
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|unique:vouchers',
                'description' => 'nullable|string',
                'type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'minimum_purchase' => 'nullable|numeric|min:0',
                'max_usage' => 'nullable|integer|min:1',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            $voucher = Voucher::create([
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'discount_value' => $validated['discount_value'],
                'minimum_purchase' => $validated['minimum_purchase'] ?? 0,
                'max_usage' => $validated['max_usage'] ?? null,
                'current_usage' => 0,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => true,
            ]);

            return response()->json([
                'message' => 'Voucher berhasil dibuat',
                'data' => $voucher
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get specific voucher
     */
    public function show($code)
    {
        try {
            $voucher = Voucher::where('code', strtoupper($code))->firstOrFail();

            return response()->json([
                'message' => 'Data voucher berhasil diambil',
                'data' => $voucher
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Voucher tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update voucher
     */
    public function update(Request $request, $id)
    {
        try {
            $voucher = Voucher::findOrFail($id);

            $validated = $request->validate([
                'code' => 'required|string|unique:vouchers,code,' . $id,
                'description' => 'nullable|string',
                'type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'minimum_purchase' => 'nullable|numeric|min:0',
                'max_usage' => 'nullable|integer|min:1',
                'is_active' => 'nullable|boolean',
            ]);

            $voucher->update([
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'] ?? $voucher->description,
                'type' => $validated['type'],
                'discount_value' => $validated['discount_value'],
                'minimum_purchase' => $validated['minimum_purchase'] ?? $voucher->minimum_purchase,
                'max_usage' => $validated['max_usage'] ?? $voucher->max_usage,
                'is_active' => $validated['is_active'] ?? $voucher->is_active,
            ]);

            return response()->json([
                'message' => 'Voucher berhasil diperbarui',
                'data' => $voucher
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Voucher tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Delete voucher
     */
    public function destroy($id)
    {
        try {
            $voucher = Voucher::findOrFail($id);
            $voucher->delete();

            return response()->json([
                'message' => 'Voucher berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Voucher tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Validate voucher code
     */
    public function validate(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string',
                'purchase_amount' => 'required|numeric|min:0',
            ]);

            $voucher = Voucher::where('code', strtoupper($validated['code']))
                             ->active()
                             ->valid()
                             ->first();

            if (!$voucher) {
                return response()->json([
                    'message' => 'Voucher tidak valid atau sudah expired'
                ], 404);
            }

            if ($validated['purchase_amount'] < $voucher->minimum_purchase) {
                return response()->json([
                    'message' => 'Pembelian minimum Rp' . number_format($voucher->minimum_purchase, 0, ',', '.') . ' diperlukan'
                ], 422);
            }

            // Calculate discount
            $discount = $voucher->type === 'percentage'
                ? ($validated['purchase_amount'] * $voucher->discount_value / 100)
                : $voucher->discount_value;

            $finalAmount = $validated['purchase_amount'] - $discount;

            return response()->json([
                'message' => 'Voucher valid',
                'data' => [
                    'voucher' => $voucher,
                    'original_amount' => (float) $validated['purchase_amount'],
                    'discount' => (float) $discount,
                    'final_amount' => (float) $finalAmount,
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}