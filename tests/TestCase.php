<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    /**
     * Helper method to authenticate a user for API requests
     */
    protected function authenticate($user = null)
    {
        $user = $user ?: User::factory()->create();
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    /**
     * Helper method to make authenticated JSON requests
     */
    protected function authJson($method, $uri, array $data = [], $user = null)
    {
        $user = $user ?: User::factory()->create();
        $this->actingAs($user, 'sanctum');
        
        return $this->json($method, $uri, $data);
    }

    /**
     * Helper method to get authentication headers
     */
    protected function getAuthHeaders($user = null)
    {
        $user = $user ?: User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}