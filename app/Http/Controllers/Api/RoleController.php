<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::with('permissions');
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }
        $roles = $query->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data semua role berhasil diambil',
            'data' => $roles->appends([
                'search' => $request->input('search'),
            ]),
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'guard_name' => 'required|string|max:255',
                'permissions' => 'required|string|exists:permissions,name',
            ]);

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => $request->guard_name,
            ]);
            // Jika permission dikirim, tambahkan ke role
            if (!empty($validated['permissions'])) {
                $role->givePermissionTo($validated['permissions']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role->load('permissions')
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
                'permissions' => 'required|string|exists:permissions,name',
            ]);

            $role = Role::findOrFail($id);
            $role->update($validatedData);

            // Jika permission dikirim, sync agar hanya 1 permission yang aktif
            if (!empty($validatedData['permissions'])) {
                $role->syncPermissions([$validatedData['permissions']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role berhasil diperbarui',
                'data' => $role->load('permissions')
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
                'message' => 'Terjadi kesalahan saat memperbarui Role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);
            // Cek apakah role masih memiliki relasi dengan user
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role tidak dapat dihapus karena masih digunakan oleh user.'
                ], 400);
            }
            // Jika role memiliki relasi dengan user atau permission, hapus dengan aman
            $role->permissions()->detach(); // lepas semua permission sebelum hapus role

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role berhasil dihapus.'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Role. Silakan coba lagi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
