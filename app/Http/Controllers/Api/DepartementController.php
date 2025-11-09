<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DepartementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Departement::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('n_departement', 'like', "%{$search}%");
        }
        $departement = $query->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data semua Departement berhasil diambil',
            'data' => $departement->appends([
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
                'n_departement' => 'required|string|max:255',
            ]);

            $departement = Departement::create([
                'n_departement' => $request->n_departement,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Departement created successfully',
                'data' => $departement
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
    public function show(Departement $departement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Departement $departement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'n_departement' => 'required|string|max:255',
            ]);

            $departement = Departement::findOrFail($id);
            $departement->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Departement berhasil diperbarui',
                'data' => $departement
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
            $departement = Departement::find($id);

            if (!$departement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departement tidak ditemukan.'
                ], 404);
            }

            if (method_exists($departement, 'jabatan') && $departement->jabatan()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departement tidak dapat dihapus karena masih terhubung dengan Jabatan.'
                ], 400);
            }

            $departement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Departement berhasil dihapus.'
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            // Jika gagal karena constraint relasi database
            return response()->json([
                'success' => false,
                'message' => 'Departement tidak dapat dihapus karena masih terhubung dengan data lain.',
                'error' => $e->getMessage()
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Departement. Silakan coba lagi.'
            ], 500);
        }
    }
}
