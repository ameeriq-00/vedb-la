<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Vehicle permissions
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',
            
            // Vehicle status permissions
            'update vehicle status',
            
            // Transfer permissions
            'create transfers',
            'view transfers',
            
            // Edit request permissions
            'create edit requests',
            'approve edit requests',
            'view edit requests',
            
            // User management
            'manage users',
            
            // Reports
            'view reports',
            'export reports',
            
            // Special permissions
            'transfer vehicle file',
            'refer vehicle externally',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // 1. Admin (المشرف)
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());
        
        // 2. Data Entry (مدخل البيانات)
        $role = Role::create(['name' => 'data_entry']);
        $role->givePermissionTo([
            'view vehicles',
            'create vehicles',
            'update vehicle status',
            'view transfers',
            'create edit requests',
            'view edit requests',
        ]);
        
        // 3. Verifier (المدقق)
        $role = Role::create(['name' => 'verifier']);
        $role->givePermissionTo([
            'view vehicles',
            'edit vehicles',
            'update vehicle status',
            'approve edit requests',
            'view edit requests',
            'transfer vehicle file',
            'view transfers',
            'view reports',
            'export reports',
        ]);
        
        // 4. Vehicles Department (الآليات)
        $role = Role::create(['name' => 'vehicles_dept']);
        $role->givePermissionTo([
            'view vehicles',
            'view transfers',
            'create transfers',
            'update vehicle status',
            'view reports',
            'create vehicles',
        ]);
        
        // 5. Recipient (المستلم)
        $role = Role::create(['name' => 'recipient']);
        $role->givePermissionTo([
            'view vehicles',
            'view transfers',
        ]);
    }
}