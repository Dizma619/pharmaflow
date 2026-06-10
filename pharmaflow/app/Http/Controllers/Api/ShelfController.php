<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shelf;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShelfController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Shelf::with('warehouse', 'stocks');

            if ($request->has('warehouse_id')) {
                $query->where('warehouse_id', $request->get('warehouse_id'));
            }

            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%$search%")
                      ->orWhere('code', 'like', "%$search%");
            }

            $shelves = $query->paginate(20);

            return response()->json([
                'message' => 'Shelves retrieved successfully',
                'data' => $shelves
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'code' => 'required|string|unique:shelves',
                'name' => 'required|string',
                'capacity' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
            ]);

            $shelf = Shelf::create($validated);

            return response()->json([
                'message' => 'Shelf created successfully',
                'data' => $shelf->load('warehouse')
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        try {
            $shelf = Shelf::with('warehouse', 'stocks.medicine')->findOrFail($id);

            return response()->json([
                'message' => 'Shelf retrieved successfully',
                'data' => $shelf
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Shelf not found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $shelf = Shelf::findOrFail($id);

            $validated = $request->validate([
                'code' => 'required|string|unique:shelves,code,' . $id,
                'name' => 'required|string',
                'capacity' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'status' => 'nullable|in:aktif,tidak_aktif',
            ]);

            $shelf->update($validated);

            return response()->json([
                'message' => 'Shelf updated successfully',
                'data' => $shelf->load('warehouse')
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Shelf not found'
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $shelf = Shelf::findOrFail($id);
            $shelf->delete();

            return response()->json([
                'message' => 'Shelf deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Shelf not found'
            ], 404);
        }
    }
}