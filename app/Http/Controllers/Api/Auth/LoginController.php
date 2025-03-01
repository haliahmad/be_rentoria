<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Handle user registration
     */
    public function register(Request $request)
{
    // Validasi input
    $validator = Validator::make($request->all(), [
        'name'                  => 'required|string|max:255',
        'email'                 => 'required|email|unique:users,email',
        'password'              => 'required|string|min:6|confirmed',
        'password_confirmation' => 'required|string|min:6'
    ]);

    // Jika validasi gagal
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    // Buat user baru dengan role_id 2 (customer)
    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
        'role_id'  => 2, // Default role untuk customer
    ]);

    // Berikan role customer
    $role = Role::where('name', 'customer')->first();
    if ($role) {
        $user->assignRole($role);
    }

    // Generate JWT token
    $token = JWTAuth::fromUser($user);

    return response()->json([
        'success' => true,
        'message' => 'Registrasi berhasil',
        'user'    => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => 'customer'
        ],
        'token' => $token
    ], 201);
}


    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'email'   => 'required|email',
            'password'=> 'required',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        // Coba login dengan kredensial yang diberikan
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password salah'
            ], 401);
        }

        $user = Auth::guard('api')->user();

        // Ambil role pengguna
        $role = $user->roles->pluck('name')->first(); // Jika user memiliki banyak role, ambil yang pertama

        return response()->json([
            'success' => true,
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $role, // Role pengguna
            ],
            'token' => $token,
        ], 200);
    }

    /**
     * Handle user logout
     */
    public function logout()
    {
        try {
            // Hapus token pengguna
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal logout, silakan coba lagi'
            ], 500);
        }
    }
}
