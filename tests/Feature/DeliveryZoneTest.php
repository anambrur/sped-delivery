<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\DeliveryZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AuthenticatesUsers;

class DeliveryZoneTest extends TestCase
{
    use RefreshDatabase, AuthenticatesUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createUserAndLogin();
    }

    #[Test]
    public function it_can_create_a_radius_delivery_zone()
    {
        $restaurant = \App\Models\Restaurant::factory()->create();

        $zoneData = [
            'restaurant_id' => $restaurant->id,
            'type' => 'radius',
            'radius' => 5000, // 5km in meters
        ];

        $response = $this->postJson('/api/delivery-zones', $zoneData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Delivery zone created successfully'
            ]);

        $this->assertDatabaseHas('delivery_zones', [
            'restaurant_id' => $restaurant->id,
            'type' => 'radius',
            'radius' => 5000
        ]);
    }

    #[Test]
    public function it_can_create_a_polygon_delivery_zone()
    {
        $restaurant = \App\Models\Restaurant::factory()->create();

        $zoneData = [
            'restaurant_id' => $restaurant->id,
            'type' => 'polygon',
            'coordinates' => [
                ['lat' => 40.7128, 'lng' => -74.0060],
                ['lat' => 40.7128, 'lng' => -74.0065],
                ['lat' => 40.7123, 'lng' => -74.0065],
                ['lat' => 40.7123, 'lng' => -74.0060],
            ]
        ];

        $response = $this->postJson('/api/delivery-zones', $zoneData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Delivery zone created successfully'
            ]);

        $this->assertDatabaseHas('delivery_zones', [
            'restaurant_id' => $restaurant->id,
            'type' => 'polygon'
        ]);
    }

    #[Test]
    public function it_validates_address_within_delivery_zone()
    {
        $restaurant = Restaurant::factory()->create([
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        DeliveryZone::factory()->create([
            'restaurant_id' => $restaurant->id,
            'type' => 'radius',
            'radius' => 5000, // 5km radius
        ]);

        $validationData = [
            'restaurant_id' => $restaurant->id,
            'latitude' => 40.7130, // Nearby point
            'longitude' => -74.0062,
        ];

        $response = $this->postJson('/api/delivery-zones/validate-address', $validationData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_within_delivery_zone' => true
            ]);
    }

    #[Test]
    public function it_validates_address_outside_delivery_zone()
    {
        $restaurant = Restaurant::factory()->create([
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        DeliveryZone::factory()->create([
            'restaurant_id' => $restaurant->id,
            'type' => 'radius',
            'radius' => 1000, // 1km radius (small)
        ]);

        $validationData = [
            'restaurant_id' => $restaurant->id,
            'latitude' => 41.0000, // Far away point
            'longitude' => -74.5000,
        ];

        $response = $this->postJson('/api/delivery-zones/validate-address', $validationData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_within_delivery_zone' => false
            ]);
    }

    #[Test]
    public function it_can_retrieve_delivery_zones_for_restaurant()
    {
        $restaurant = \App\Models\Restaurant::factory()->create();
        DeliveryZone::factory()->count(3)->create([
            'restaurant_id' => $restaurant->id
        ]);

        // Change this line:
        $response = $this->getJson("/api/delivery-zones/restaurant/{$restaurant->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_can_retrieve_all_delivery_zones()
    {
        DeliveryZone::factory()->count(3)->create();

        $response = $this->getJson('/api/delivery-zones');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_can_retrieve_specific_delivery_zone()
    {
        $zone = DeliveryZone::factory()->create();

        $response = $this->getJson("/api/delivery-zones/{$zone->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $zone->id,
                    'type' => $zone->type
                ]
            ]);
    }

    #[Test]
    public function it_can_update_delivery_zone()
    {
        $zone = DeliveryZone::factory()->create(['type' => 'radius']);

        $updateData = [
            'type' => 'polygon',
            'coordinates' => [
                ['lat' => 40.7128, 'lng' => -74.0060],
                ['lat' => 40.7128, 'lng' => -74.0065],
                ['lat' => 40.7123, 'lng' => -74.0065],
                ['lat' => 40.7123, 'lng' => -74.0060],
            ]
            // Remove 'radius' => null from the request
        ];

        $response = $this->putJson("/api/delivery-zones/{$zone->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Delivery zone updated successfully'
            ]);

        $this->assertDatabaseHas('delivery_zones', [
            'id' => $zone->id,
            'type' => 'polygon',
            'radius' => null
        ]);
    }

    #[Test]
    public function it_can_delete_delivery_zone()
    {
        $zone = DeliveryZone::factory()->create();

        $response = $this->deleteJson("/api/delivery-zones/{$zone->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Delivery zone deleted successfully'
            ]);

        $this->assertDatabaseMissing('delivery_zones', ['id' => $zone->id]);
    }
}
