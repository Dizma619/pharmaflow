<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers
     */
    public function index(Request $request)
    {
        try {
            $query = Supplier::withCount('medicines', 'purchases');

            // Search
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('phone', 'like', "%$search%");
            }

            // Filter active
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $suppliers = $query->paginate($perPage);

            return response()->json([
                'message' => 'Data supplier berhasil diambil',
                'data' => $suppliers
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created supplier
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:suppliers',
                'contact_person' => 'required|string',
                'email' => 'nullable|email',
                'phone' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'province' => 'required|string',
                'postal_code' => 'required|string',
                'bank_name' => 'nullable|string',
                'bank_account' => 'nullable|string',
                'payment_terms' => 'nullable|string',
            ]);

            $supplier = Supplier::create([
                'name' => $validated['name'],
                'contact_person' => $validated['contact_person'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'],
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account' => $validated['bank_account'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? '30 hari',
                'is_active' => true,
            ]);

            return response()->json([
                'message' => 'Supplier berhasil dibuat',
                'data' => $supplier
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified supplier
     */
    public function show($id)
    {
        try {
            $supplier = Supplier::with('medicines', 'purchases')->findOrFail($id);

            return response()->json([
                'message' => 'Data supplier berhasil diambil',
                'data' => $supplier
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Supplier tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified supplier
     */
    public function update(Request $request, $id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|unique:suppliers,name,' . $id,
                'contact_person' => 'required|string',
                'email' => 'nullable|email',
                'phone' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'province' => 'required|string',
                'postal_code' => 'required|string',
                'bank_name' => 'nullable|string',
                'bank_account' => 'nullable|string',
                'payment_terms' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            $supplier->update([
                'name' => $validated['name'],
                'contact_person' => $validated['contact_person'],
                'email' => $validated['email'] ?? $supplier->email,
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'],
                'bank_name' => $validated['bank_name'] ?? $supplier->bank_name,
                'bank_account' => $validated['bank_account'] ?? $supplier->bank_account,
                'payment_terms' => $validated['payment_terms'] ?? $supplier->payment_terms,
                'is_active' => $validated['is_active'] ?? $supplier->is_active,
            ]);

            return response()->json([
                'message' => 'Supplier berhasil diperbarui',
                'data' => $supplier
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Supplier tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Remove the specified supplier
     */
    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            // Check if supplier has medicines
            if ($supplier->medicines()->count() > 0) {
                return response()->json([
                    'message' => 'Supplier tidak dapat dihapus karena masih memiliki obat'
                ], 422);
            }

            // Check if supplier has purchases
            if ($supplier->purchases()->count() > 0) {
                return response()->json([
                    'message' => 'Supplier tidak dapat dihapus karena masih memiliki pembelian'
                ], 422);
            }

            $supplier->delete();

            return response()->json([
                'message' => 'Supplier berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Supplier tidak ditemukan'
            ], 404);
        }
    }
}