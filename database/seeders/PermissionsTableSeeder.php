<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permissions untuk item (CRUD)
        Permission::create(['name' => 'item.index', 'guard_name' => 'api']);   // Melihat daftar item
        Permission::create(['name' => 'item.create', 'guard_name' => 'api']);  // Membuat item baru
        Permission::create(['name' => 'item.edit', 'guard_name' => 'api']);    // Mengedit item
        Permission::create(['name' => 'item.delete', 'guard_name' => 'api']);  // Menghapus item

    }
}
