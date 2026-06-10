<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PromotionController extends Controller
{
    /**
     * Get active promotions
     */
    public function index(Request $request)
    {
        try {
            $query = Promotion::with('medicine');

            // Get only active
            if (!$request->has('all')) {
                $query->active();
            }

            // Filter by medicine
            if ($request->has('medicine_id')) {
                $query->where('medicine_id', $request->get('medicine_id'));
            }

            // Search
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            }

            $promotions = $query->orderBy('start_date', 'desc')->paginate(15);

            return response()->json([
                'message' => 'Data promo berhasil diambil',
                'data' => $promotions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create promotion
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'medicine_id' => 'nullable|exists:medicines,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'max_quantity' => 'nullable|integer|min:1',
            ]);

            $promotion = Promotion::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'discount_value' => $validated['discount_value'],
                'medicine_id' => $validated['medicine_id'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'max_quantity' => $validated['max_quantity'] ?? null,
                'is_active' => true,
                'usage_count' => 0,
            ]);

            // Notify all customers
            $this->notifyCustomers($promotion);

            return response()->json([
                'message' => 'Promo berhasil dibuat',
                'data' => $promotion->load('medicine')
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
     * Get specific promotion
     */
    public function show($id)
    {
        try {
            $promotion = Promotion::with('medicine')->findOrFail($id);

            return response()->json([
                'message' => 'Data promo berhasil diambil',
                'data' => $promotion
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Promo tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update promotion
     */
    public function update(Request $request, $id)
    {
        try {
            $promotion = Promotion::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'end_date' => 'required|date|after:start_date',
                'max_quantity' => 'nullable|integer|min:1',
                'is_active' => 'nullable|boolean',
            ]);

            $promotion->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? $promotion->description,
                'type' => $validated['type'],
                'discount_value' => $validated['discount_value'],
                'end_date' => $validated['end_date'],
                'max_quantity' => $validated['max_quantity'] ?? $promotion->max_quantity,
                'is_active' => $validated['is_active'] ?? $promotion->is_active,
            ]);

            return response()->json([
                'message' => 'Promo berhasil diperbarui',
                'data' => $promotion->load('medicine')
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Promo tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Delete promotion
     */
    public function destroy($id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->delete();

            return response()->json([
                'message' => 'Promo berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Promo tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Notify customers about promotion
     */
    private function notifyCustomers($promotion)
    {
        $customers = User::where('role', 'customer')->get();

        foreach ($customers as $customer) {
            Notification::create([
                'user_id' => $customer->id,
                'type' => 'promo_baru',
                'title' => 'Promo Baru!',
                'message' => "{$promotion->name} - Dapatkan diskon hingga " . ($promotion->type === 'percentage' ? $promotion->discount_value . '%' : 'Rp' . number_format($promotion->discount_value, 0, ',', '.')),
                'data' => json_encode(['promotion_id' => $promotion->id])
            ]);
        }
    }
}