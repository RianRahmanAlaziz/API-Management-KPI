<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"User"},
     *     summary="Ambil semua data user",
     *     description="Mengambil semua data user dari database.",
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil semua user"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = User::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }
        $users = $query->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data semua user berhasil diambil',
            'data' => $users->appends([
                'search' => $request->input('search'),
            ]),
        ], 200);
    }
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Register user baru",
     *     description="Membuat user baru di sistem.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="Rian Rahman"),
     *             @OA\Property(property="email", type="string", example="rian@mail.com"),
     *             @OA\Property(property="password", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User created successfully")
     * )
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'roles' => 'required',
        ]);
        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);
        $user->assignRole($request->roles);

        return response()->json(['message' => 'User created successfully'], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"User"},
     *     summary="Update user berdasarkan ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email"},
     *             @OA\Property(property="name", type="string", example="Rian Update"),
     *             @OA\Property(property="email", type="string", example="rianupdate@mail.com"),
     *             @OA\Property(property="password", type="string", example="newpassword")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User berhasil diperbarui")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|max:255',
                'email' => 'required|email',
                'password' => 'nullable|min:2'
            ]);

            if ($request->filled('password')) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            } else {
                unset($validatedData['password']);
            }

            $user = User::findOrFail($id);
            $user->roles()->detach();
            $user->update($validatedData);

            if ($request->filled('roles')) {
                $user->assignRole($request->roles); // Menggunakan syncRoles untuk mengganti peran yang ada
            }

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui',
                'data' => $user
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
                'message' => 'Terjadi kesalahan saat memperbarui user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"User"},
     *     summary="Hapus user berdasarkan ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID user yang akan dihapus",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User berhasil dihapus"),
     *     @OA\Response(response=404, description="User tidak ditemukan")
     * )
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan.'
                ], 404);
            }
            if (method_exists($user, 'roles')) {
                $user->roles()->detach();
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="rian@mail.com"),
     *             @OA\Property(property="password", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login berhasil"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Gunakan guard 'api' secara eksplisit (JWT)
        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout user",
     *     @OA\Response(response=200, description="Successfully logged out")
     * )
     */
    public function logout(Request $request)
    {
        Auth::guard('api')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     tags={"Auth"},
     *     summary="Refresh token JWT",
     *     @OA\Response(response=200, description="Token berhasil diperbarui")
     * )
     */
    public function refresh()
    {
        try {
            // Ambil token lama dan buat token baru
            $newToken = JWTAuth::parseToken()->refresh();

            return $this->respondWithToken($newToken);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Token tidak valid atau sudah kedaluwarsa'
            ], 401);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }
}
