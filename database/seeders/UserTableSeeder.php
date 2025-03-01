<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserTableSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk menambahkan user dengan role dan permission.
     */
    public function run(): void
    {
        // Ambil role berdasarkan ID atau nama
        $adminRole = Role::where('name', 'admin')->first();
        $customerRole = Role::where('name', 'customer')->first();

        // Tentukan permission untuk admin dan customer
        $adminPermissions = Permission::all(); // Admin mendapatkan semua permission
        $customerPermissions = Permission::whereIn('name', ['item.index', 'item.create'])->get(); // Customer hanya bisa melihat dan membuat item

        // Berikan permission ke role masing-masing
        if ($adminRole) {
            $adminRole->syncPermissions($adminPermissions);
        }

        if ($customerRole) {
            $customerRole->syncPermissions($customerPermissions);
        }

        // Buat user dengan role admin
        $adminUser = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole ? $adminRole->id : 1, // Default role_id 1 untuk admin
        ]);
        $adminUser->assignRole('admin');

        // Buat user dengan role customer
        $customerUser = User::create([
            'name' => 'Customer',
            'email' => 'customer@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $customerRole ? $customerRole->id : 2, // Default role_id 2 untuk customer
        ]);
        $customerUser->assignRole('customer');
    }
}
