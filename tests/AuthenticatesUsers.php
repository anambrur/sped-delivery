<?php

namespace Tests;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Laravel\Sanctum\Sanctum;

trait AuthenticatesUsers
{
    /**
     * Create a user and authenticate with Sanctum with admin permissions
     */
    protected function createUserAndLogin()
    {
        $user = User::factory()->create();

        // Create or get admin role and assign all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Create all necessary permissions
        $permissions = [
            'delivery-men.create',
            'delivery-men.view',
            'delivery-men.edit',
            'delivery-men.delete',
            'delivery-zones.create',
            'delivery-zones.view',
            'delivery-zones.edit',
            'delivery-zones.delete',
            'orders.create',
            'orders.view',
            'orders.edit',
            'orders.delete',
            'restaurants.create',
            'restaurants.view',
            'restaurants.edit',
            'restaurants.delete'
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $adminRole->givePermissionTo(Permission::all());
        $user->assignRole($adminRole);

        $this->actingAs($user, 'sanctum');
        return $user;
    }

    /**
     * Create a user with specific role and permissions
     */
    protected function createUserWithRole($roleName, $permissions = [])
    {
        $user = User::factory()->create();

        $role = Role::firstOrCreate(['name' => $roleName]);

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $role->givePermissionTo($permissions);
        $user->assignRole($role);

        $this->actingAs($user, 'sanctum');
        return $user;
    }

    /**
     * Get authentication headers for API requests with admin permissions
     */
    protected function getAuthHeaders($user = null)
    {
        $user = $user ?: User::factory()->create();

        // Assign admin role with all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());
        $user->assignRole($adminRole);

        $token = $user->createToken('test-token')->plainTextToken;

        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
    }
}
