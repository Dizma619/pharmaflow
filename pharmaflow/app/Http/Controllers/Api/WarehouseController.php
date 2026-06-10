<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    /**
     * Get all warehouses
     */
    public function index()
    {
        try {
            // Kita bungkus dengan pengaman. Jika relasi 'shelves' bermasalah, kita tangkap errornya.
            // Pastikan di model Warehouse sudah ada function shelves()
            $warehouses = Warehouse::withCount('shelves')->latest()->get();

            return response()->json([
                'success' => true,
                'message' => 'Warehouses retrieved successfully',
                'data'    => $warehouses
            ], 200);

        } catch (\Exception $e) {
            // JIKA CRASH, KODE INI AKAN MENAMPILKAN PESAN ERROR ASLINYA DI BROWSER
            return response()->json([
                'success' => false,
                'message' => 'Error di index: ' . $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ], 500);
        }
    }

    /**
     * Store warehouse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'city'     => 'required|string|max:255',
                'province' => 'required|string|max:255',
                'address'  => 'nullable|string',
                'capacity' => 'nullable|integer|min:0',
                'status'   => 'nullable|string'
            ]);

            if (!isset($validated['status'])) {
                $validated['status'] = 'aktif';
            }

            $warehouse = Warehouse::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse created successfully',
                'data'    => $warehouse
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

    /**
     * Show warehouse
     */
    public function show(string $id)
    {
        try {
            $warehouse = Warehouse::withCount('shelves')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse retrieved',
                'data'    => $warehouse
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update warehouse
     */
    public function update(Request $request, string $id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);

            // Membaca payload request dengan aman
            $input = $request->isJson() ? $request->json()->all() : $request->all();

            $validated = Validator::make($input, [
                'name'     => 'required|string|max:255',
                'city'     => 'required|string|max:255',
                'province' => 'required|string|max:255',
                'address'  => 'nullable|string',
                'capacity' => 'nullable|integer|min:0',
                'status'   => 'nullable|string'
            ])->validate();

            $warehouse->update($validated);
            $warehouse->loadCount('shelves');

            return response()->json([
                'success' => true,
                'message' => 'Warehouse updated successfully',
                'data'    => $warehouse
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $v) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $v->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete warehouse
     */
    public function destroy(string $id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            $warehouse->delete();

            return response()->json([
                'success' => true,
                'message' => 'Warehouse deleted'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}