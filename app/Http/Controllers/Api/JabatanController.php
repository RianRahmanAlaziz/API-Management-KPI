<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Jabatan::with('departement');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('n_jabatan', 'like', "%{$search}%");
        }
        $jabatan = $query->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data semua Jabatan berhasil diambil',
            'data' => $jabatan->appends([
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
                'n_jabatan' => 'required|string|max:255',
                'departement_id' => 'required',
            ]);

            $jabatan = Jabatan::create([
                'departement_id' => $request->departement_id,
                'n_jabatan' => $request->n_jabatan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jabatan created successfully',
                'data' => $jabatan
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
    public function show(Jabatan $jabatan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jabatan $jabatan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Jabatan $jabatan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jabatan $jabatan)
    {
        //
    }
}
