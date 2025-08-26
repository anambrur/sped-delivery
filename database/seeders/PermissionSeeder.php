<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear all permission-related data and demo users
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        Permission::truncate();
        Role::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define the modules
        $modules = [
            'roles',
            'users',
            'permission',
            'restaurants',
            'delivery-zones',
            'delivery-men',
            'orders',
        ];

        // Define actions
        $actions = ['create', 'view', 'edit', 'delete'];

        // Create permissions dynamically
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::create(['name' => "$module.$action", 'guard_name' => 'web']);
            }
        }

        // Create roles
        $roles = Role::insert([
            ['name' => 'admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'restaurant-owner', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delivery-man', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Get role instances
        $roleAdmin = Role::where('name', 'admin')->first();
        $roleRestaurantOwner = Role::where('name', 'restaurant-owner')->first();
        $roleDeliveryMan = Role::where('name', 'delivery-man')->first();

        // Assign all permissions to admin
        $roleAdmin->givePermissionTo(Permission::all());

        // Assign specific permissions to restaurant owner
        $restaurantOwnerPermissions = [
            'restaurants.view',
            'restaurants.edit',
            'orders.view',
            'orders.edit'
        ];
        $roleRestaurantOwner->givePermissionTo($restaurantOwnerPermissions);

        // Assign specific permissions to delivery man
        $deliveryManPermissions = [
            'orders.view',
            'orders.edit'
        ];
        $roleDeliveryMan->givePermissionTo($deliveryManPermissions);

        // Create admin user
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        $adminUser->assignRole($roleAdmin);

        // Create restaurant owner user
        $restaurantOwnerUser = User::factory()->create([
            'name' => 'Restaurant Owner',
            'email' => 'restaurant-owner@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        $restaurantOwnerUser->assignRole($roleRestaurantOwner);

        // Create delivery man user
        $deliveryManUser = User::factory()->create([
            'name' => 'Delivery Man',
            'email' => 'delivery-man@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        $deliveryManUser->assignRole($roleDeliveryMan);

        
    }
}