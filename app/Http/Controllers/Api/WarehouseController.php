<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Get all warehouses
     */
    public function index()
    {
        try {

            $warehouses =
                Warehouse::latest()
                ->get();

            return response()->json([
                'message' =>
                    'Warehouses retrieved successfully',

                'data' =>
                    $warehouses
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                    $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store warehouse
     */
    public function store(Request $request)
    {
        try {

            $validated =
                $request->validate([
                    'name' =>
                        'required|string|max:255',

                    'location' =>
                        'nullable|string'
                ]);

            $warehouse =
                Warehouse::create(
                    $validated
                );

            return response()->json([
                'message' =>
                    'Warehouse created successfully',

                'data' =>
                    $warehouse
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                    $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show warehouse
     */
    public function show(string $id)
    {
        try {

            $warehouse =
                Warehouse::findOrFail($id);

            return response()->json([
                'message' =>
                    'Warehouse retrieved',

                'data' =>
                    $warehouse
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                    $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update warehouse
     */
    public function update(
        Request $request,
        string $id
    ) {
        try {

            $warehouse =
                Warehouse::findOrFail($id);

            $validated =
                $request->validate([
                    'name' =>
                        'required|string|max:255',

                    'location' =>
                        'nullable|string'
                ]);

            $warehouse->update(
                $validated
            );

            return response()->json([
                'message' =>
                    'Warehouse updated',

                'data' =>
                    $warehouse
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                    $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete warehouse
     */
    public function destroy(string $id)
    {
        try {

            $warehouse =
                Warehouse::findOrFail($id);

            $warehouse->delete();

            return response()->json([
                'message' =>
                    'Warehouse deleted'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                    $e->getMessage()
            ], 500);
        }
    }
}
