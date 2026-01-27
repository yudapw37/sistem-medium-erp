<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ApprovalRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create approval permissions
        $approvalPermissions = [
            'sales-approve-finance',
            'sales-approve-warehouse',
            'sales-approval-access',
        ];

        foreach ($approvalPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Finance role
        $financeRole = Role::firstOrCreate(['name' => 'finance']);
        $financeRole->givePermissionTo([
            'dashboard-access',
            'sales-approval-access',
            'sales-approve-finance',
            'transactions-access',
            'reports-access',
        ]);

        // Create Warehouse role
        $warehouseRole = Role::firstOrCreate(['name' => 'warehouse']);
        $warehouseRole->givePermissionTo([
            'dashboard-access',
            'sales-approval-access',
            'sales-approve-warehouse',
            'transactions-access',
            'products-access',
        ]);

        // Give admin and super-admin all approval permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($approvalPermissions);
        }

        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($approvalPermissions);
        }

        $this->command->info('Approval roles and permissions created successfully!');
    }
}
