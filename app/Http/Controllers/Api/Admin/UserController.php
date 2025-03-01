<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $authenticatedUser = $request->user();
            $authenticatedUserRole = $authenticatedUser->role->name;
            
            $query = User::with('role');
            
            if ($authenticatedUserRole === 'admin') {
                $roleFilter = $request->input('roleCategory');
                if ($roleFilter) {
                    $query->whereHas('role', function ($q) use ($roleFilter) {
                        $q->where('name', $roleFilter);
                    });
                }
                $searchTerm = $request->input('searchTerm');
                if ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%');
                }
                $users = $query->get();
            } else {
                if ($authenticatedUserRole === 'customer') {
                    $query->where('id', $authenticatedUser->id);
                }
                $users = $query->get();
            }
            
            return response()->json(['users' => $users], 200);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Error fetching users'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $formData = $request->all();
            
            $user = User::create([
                'name' => $formData['name'],
                'email' => $formData['email'],
                'password' => Hash::make($formData['password']),
                'role_id' => $formData['role_id'] ?? 2,
            ]);
            
            $role = Role::find($formData['role_id'] ?? 2);
            if (!$role) {
                return response()->json(['message' => 'Invalid role provided.'], 422);
            }
            $user->assignRole($role->name);
            
            return response()->json(['user' => $user, 'message' => 'User created successfully'], 201);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }
            return response()->json(['user' => $user], 200);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Error fetching user'], 500);
        }
    }

    public function update(Request $request, $id)
{
    try {
        // Validasi request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        // Cek apakah user ada
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Update data user
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Cek role
        if (!empty($validatedData['role_id'])) {
            $role = Role::find($validatedData['role_id']);
            if (!$role) {
                return response()->json(['message' => 'Invalid role provided.'], 422);
            }
            $user->syncRoles([$role->name]);
            $user->role_id = $role->id;
        }

        $user->save();

        return response()->json(['user' => $user, 'message' => 'User updated successfully']);
    } catch (\Exception $exception) {
        return response()->json(['message' => 'Error updating user', 'error' => $exception->getMessage()], 500);
    }
}


public function destroy($id)
{
    try {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    } catch (\Exception $exception) {
        return response()->json(['message' => 'Error deleting user', 'error' => $exception->getMessage()], 500);
    }
}

}
