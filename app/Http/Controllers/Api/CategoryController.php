<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        try {
            $query = Category::query();

            // Search
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            }

            // Filter active
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $categories = $query->paginate($perPage);

            return response()->json([
                'message' => 'Data kategori berhasil diambil',
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:categories',
                'description' => 'nullable|string',
                'icon' => 'nullable|string',
            ]);

            $category = Category::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'icon' => $validated['icon'] ?? null,
                'slug' => Str::slug($validated['name']),
                'is_active' => true,
            ]);

            return response()->json([
                'message' => 'Kategori berhasil dibuat',
                'data' => $category
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified category
     */
    public function show($id)
    {
        try {
            $category = Category::with('medicines')->findOrFail($id);

            return response()->json([
                'message' => 'Data kategori berhasil diambil',
                'data' => $category
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|unique:categories,name,' . $id,
                'description' => 'nullable|string',
                'icon' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            $category->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? $category->description,
                'icon' => $validated['icon'] ?? $category->icon,
                'slug' => Str::slug($validated['name']),
                'is_active' => $validated['is_active'] ?? $category->is_active,
            ]);

            return response()->json([
                'message' => 'Kategori berhasil diperbarui',
                'data' => $category
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);

            // Check if category has medicines
            if ($category->medicines()->count() > 0) {
                return response()->json([
                    'message' => 'Kategori tidak dapat dihapus karena masih memiliki obat'
                ], 422);
            }

            $category->delete();

            return response()->json([
                'message' => 'Kategori berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }
    }
}