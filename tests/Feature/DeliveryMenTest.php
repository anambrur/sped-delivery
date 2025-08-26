<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DeliveryMen;
use Tests\AuthenticatesUsers;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryMenTest extends TestCase
{
    use RefreshDatabase, AuthenticatesUsers;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary permissions first
        $permissions = [
            'delivery-men.create',
            'delivery-men.view',
            'delivery-men.edit',
            'delivery-men.delete'
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $this->createUserAndLogin();
    }

    #[Test]
    public function it_can_create_a_delivery_man()
    {
        $deliveryManData = [
            'name' => 'John Delivery',
            'phone' => '+1234567890',
            'vehicle_type' => 'motorcycle',
            'capacity' => 5,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'is_available' => true,
        ];

        $response = $this->postJson('/api/delivery-men', $deliveryManData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Delivery person created successfully'
            ]);

        $this->assertDatabaseHas('delivery_mens', [
            'name' => 'John Delivery',
            'phone' => '+1234567890',
            'vehicle_type' => 'motorcycle',
        ]);
    }

    #[Test]
    public function it_can_retrieve_available_delivery_men()
    {
        DeliveryMen::factory()->count(2)->create(['is_available' => true]);
        DeliveryMen::factory()->count(1)->create(['is_available' => false]);

        $response = $this->getJson('/api/delivery-men/available');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_can_find_nearby_delivery_men()
    {
        // Create delivery men at different locations
        DeliveryMen::factory()->create([
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'is_available' => true
        ]);

        DeliveryMen::factory()->create([
            'latitude' => 40.7125,
            'longitude' => -74.0058,
            'is_available' => true
        ]);

        // One far away delivery man
        DeliveryMen::factory()->create([
            'latitude' => 41.0000,
            'longitude' => -74.5000,
            'is_available' => true
        ]);

        $queryParams = [
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'radius' => 10, // 10km radius
            'only_available' => true
        ];

        $response = $this->getJson('/api/delivery-men/nearby?' . http_build_query($queryParams));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonCount(2, 'data'); // Should find 2 nearby delivery men
    }

    #[Test]
    public function it_can_update_delivery_man_availability()
    {
        $deliveryMan = DeliveryMen::factory()->create(['is_available' => true]);

        // Change from putJson to postJson
        $response = $this->postJson("/api/delivery-men/{$deliveryMan->id}/availability", [
            'is_available' => false
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Availability updated successfully'
            ]);

        $this->assertDatabaseHas('delivery_mens', [
            'id' => $deliveryMan->id,
            'is_available' => false
        ]);
    }

    #[Test]
    public function it_can_retrieve_all_delivery_men()
    {
        DeliveryMen::factory()->count(3)->create();

        $response = $this->getJson('/api/delivery-men');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_can_retrieve_specific_delivery_man()
    {
        $deliveryMan = DeliveryMen::factory()->create();

        $response = $this->getJson("/api/delivery-men/{$deliveryMan->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $deliveryMan->id,
                    'name' => $deliveryMan->name
                ]
            ]);
    }

    #[Test]
    public function it_can_update_delivery_man()
    {
        $deliveryMan = DeliveryMen::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'vehicle_type' => 'car',
            'capacity' => 8
        ];

        $response = $this->putJson("/api/delivery-men/{$deliveryMan->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Delivery person updated successfully'
            ]);

        $this->assertDatabaseHas('delivery_mens', [
            'id' => $deliveryMan->id,
            'name' => 'Updated Name',
            'vehicle_type' => 'car',
            'capacity' => 8
        ]);
    }

    #[Test]
    public function it_can_delete_delivery_man()
    {
        $deliveryMan = DeliveryMen::factory()->create();

        $response = $this->deleteJson("/api/delivery-men/{$deliveryMan->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Delivery person deleted successfully'
            ]);

        $this->assertDatabaseMissing('delivery_mens', ['id' => $deliveryMan->id]);
    }
}
