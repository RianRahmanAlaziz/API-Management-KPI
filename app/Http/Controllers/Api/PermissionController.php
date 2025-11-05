<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }
        $permission = $query->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data semua Permission berhasil diambil',
            'data' => $permission->appends([
                'search' => $request->input('search'),
            ]),
        ], 200);
    }

    public function store(Request $request)
    {

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'guard_name' => 'required|string|max:255',
            ]);

            $permission = Permission::create([
                'name' => $request->name,
                'guard_name' => $request->guard_name,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => $permission
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

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'guard_name' => 'required|string|max:255',
            ]);

            $permission = Permission::findOrFail($id);
            $permission->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil diperbarui',
                'data' => $permission
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
                'message' => 'Terjadi kesalahan saat memperbarui Permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $permission = Permission::find($id);

            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission tidak ditemukan.'
                ], 404);
            }

            // 🔒 Cek apakah permission masih terhubung dengan role
            if ($permission->roles()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission tidak dapat dihapus karena masih terhubung dengan satu atau lebih role.'
                ], 400);
            }

            $permission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Permission. Silakan coba lagi.'
            ], 500);
        }
    }
}
