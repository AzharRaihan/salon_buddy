<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'attendance-management' => [
                'attendance-create',
                'attendance-delete',
                'attendance-edit', 
                'attendance-list',
            ],
            'booking-management' => [
                'booking-create',
                'booking-delete', 
                'booking-edit',
                'booking-list',
                'booking-calendar',
            ],
            'branch-management' => [
                'branch-create',
                'branch-delete', 
                'branch-edit',
                'branch-list',
            ],
            'category-management' => [
                'category-create',
                'category-delete', 
                'category-edit',
                'category-list',
            ],
            'customer-management' => [
                'customer-create',
                'customer-delete', 
                'customer-edit',
                'customer-list',
            ],
            'customer_receive-management' => [
                'customer_receive-create',
                'customer_receive-delete', 
                'customer_receive-edit',
                'customer_receive-list',
            ],
            'dashboard' => [
                'dashboard',
            ],
            'damage-management' => [
                'damage-create',
                'damage-delete',
                'damage-edit', 
                'damage-list',
            ],
            'employee-management' => [
                'employee-create',
                'employee-delete',
                'employee-edit',
                'employee-list',
            ],
            'expense-management' => [
                'expense-create',
                'expense-delete', 
                'expense-edit',
                'expense-list',
            ],
            'expense_category-management' => [
                'expense_category-create',
                'expense_category-delete', 
                'expense_category-edit',
                'expense_category-list',
            ],
            'item-management' => [
                'item-create',
                'item-delete',
                'item-edit', 
                'item-list',
            ],
            'payment_method-management' => [
                'payment_method-create',
                'payment_method-delete',
                'payment_method-edit', 
                'payment_method-list',
            ],
            'POS' => [
                'POS',
            ],
            'promotion-management' => [
                'promotion-create',
                'promotion-delete',
                'promotion-edit', 
                'promotion-list',
            ],
            'purchase-management' => [
                'purchase-create',
                'purchase-delete',
                'purchase-edit', 
                'purchase-list',
            ],
            'report-management' => [
                'report-attendance',
                'report-commission',
                'report-expense',
                'report-purchase',
                'report-profit-loss',
                'report-damage',
                'report-sale',
                'report-salary',
                'report-stock',
                'report-alert',
                'report-daily_summary',
            ],
            'role-management' => [
                'role-create',
                'role-delete',
                'role-edit',
                'role-list',
            ],
            'salary-management' => [
                'salary-create',
                'salary-delete',
                'salary-edit', 
                'salary-list',
            ],
            'sales-management' => [
                'sales-create',
                'sales-delete',
                'sales-edit',
                'sales-list',
            ],
            'settings' => [
                'settings-settings',
                'settings-tax',
                'settings-email',
                'settings-sms',
                'settings-whatsapp',
                'settings-payment',
                'settings-social_auth',
                'settings-white_label',
                'settings-vacation',
                'settings-holiday',
            ],
            'stock-management' => [
                'stock-stock',
                'stock-alert',
                'stock-product_usages',
            ],
            'supplier-management' => [
                'supplier-create',
                'supplier-delete', 
                'supplier-edit',
                'supplier-list',
            ],
            'supplier_payment-management' => [
                'supplier_payment-create',
                'supplier_payment-delete', 
                'supplier_payment-edit',
                'supplier_payment-list',
            ],
            'unit-management' => [
                'unit-create',
                'unit-delete',
                'unit-edit',
                'unit-list',
            ],
            
            'website-management' => [
                'website-settings',
                'website-aboutus',
                'website-banner',
                'website-delivery_area',
                'website-delivery_partner',
                'website-faq',
                'website-workingprocess',
                'website-portfolio',
            ],
        ];

        foreach ($permissions as $group => $permissions) {
            foreach ($permissions as $permission) {
                Permission::create([
                    'name' => $permission,
                    'group_name' => $group,
                    'guard_name' => 'sanctum' // Add guard_name here
                ]);
            }
        }

        // Create Super Admin role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'sanctum',
            'user_id' => 1,
            'company_id' => 1,
        ]);

        $allPermissions = Permission::all();
        if ($allPermissions->isEmpty()) {
            throw new \Exception('No permissions found to sync');
        }
        $superAdminRole->syncPermissions($allPermissions);

        // Find and assign role to user with ID 1
        $user = User::find(1);
        if (!$user) {
            throw new \Exception('User with ID 1 not found');
        }
        $user->assignRole($superAdminRole);

        // Clear cache after role assignment
        // app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
