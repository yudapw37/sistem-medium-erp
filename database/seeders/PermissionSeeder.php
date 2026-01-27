<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard-access',
            'users-access',
            'users-create',
            'users-update',
            'users-delete',
            'roles-access',
            'roles-create',
            'roles-update',
            'roles-delete',
            'permissions-access',
            'permissions-create',
            'permissions-update',
            'permissions-delete',
            'categories-access',
            'categories-create',
            'categories-edit',
            'categories-delete',
            'products-access',
            'products-create',
            'products-edit',
            'products-delete',
            'customers-access',
            'customers-create',
            'customers-edit',
            'customers-delete',
            'transactions-access',
            'reports-access',
            'profits-access',
            'payment-settings-access',
        ];

        foreach ($permissions as $permission) {
            if (!\Illuminate\Support\Facades\DB::table('permissions')->where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
            }
        }
    }
}
