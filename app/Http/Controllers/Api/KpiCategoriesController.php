<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiCategories;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KpiCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = KpiCategories::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }
        $KpiCategories = $query->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data semua Kpi Categories berhasil diambil',
            'data' => $KpiCategories->appends([
                'search' => $request->input('search'),
            ]),
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable',
            ]);

            $KpiCategories = KpiCategories::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kpi Categories created successfully',
                'data' => $KpiCategories
            ], 201);
        } catch (ValidationException $e) {
            // ✅ Kirim response validasi manual
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(KpiCategories $kpiCategories)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KpiCategories $kpiCategories)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable',
            ]);

            $KpiCategories = KpiCategories::findOrFail($id);
            $KpiCategories->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Kpi Categories berhasil diperbarui',
                'data' => $KpiCategories
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui Departement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $KpiCategories = KpiCategories::find($id);

            if (!$KpiCategories) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kpi Categories tidak ditemukan.'
                ], 404);
            }

            if (method_exists($KpiCategories, 'KpiIndicator') && $KpiCategories->KpiIndicator()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kpi Categories tidak dapat dihapus karena masih terhubung dengan Jabatan.'
                ], 400);
            }

            $KpiCategories->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kpi Categories berhasil dihapus.'
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            // Jika gagal karena constraint relasi database
            return response()->json([
                'success' => false,
                'message' => 'Kpi Categories tidak dapat dihapus karena masih terhubung dengan data lain.',
                'error' => $e->getMessage()
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus KpiCategories. Silakan coba lagi.'
            ], 500);
        }
    }
}
