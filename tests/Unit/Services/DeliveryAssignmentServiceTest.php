<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\DeliveryMen;
use App\Services\DeliveryAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class DeliveryAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $deliveryAssignmentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deliveryAssignmentService = new DeliveryAssignmentService();
    }

    // #[Test]
    // public function it_assigns_nearest_available_delivery_person()
    // {
    //     $restaurant = Restaurant::factory()->create([
    //         'latitude' => 40.7128,
    //         'longitude' => -74.0060,
    //     ]);

    //     $order = Order::factory()->create([
    //         'restaurant_id' => $restaurant->id,
    //         'delivery_latitude' => 40.7130,
    //         'delivery_longitude' => -74.0062,
    //         'delivery_men_id' => null,
    //         'status' => 'pending'
    //     ]);

    //     // Create delivery men at different distances
    //     $nearbyDeliveryMan = DeliveryMen::factory()->create([
    //         'latitude' => 40.7125, // Very close
    //         'longitude' => -74.0058,
    //         'is_available' => true
    //     ]);

    //     $farDeliveryMan = DeliveryMen::factory()->create([
    //         'latitude' => 40.7200, // Further away
    //         'longitude' => -74.0100,
    //         'is_available' => true
    //     ]);

    //     $assignedDeliveryMan = $this->deliveryAssignmentService->assignDeliveryPerson($order);

    //     $this->assertNotNull($assignedDeliveryMan);
    //     $this->assertEquals($nearbyDeliveryMan->id, $assignedDeliveryMan->id);

    //     // Refresh order from database
    //     $order->refresh();
    //     $this->assertEquals($nearbyDeliveryMan->id, $order->delivery_men_id);
    //     $this->assertEquals('assigned', $order->status);

    //     // Delivery man should be marked as unavailable
    //     $this->assertDatabaseHas('delivery_mens', [
    //         'id' => $nearbyDeliveryMan->id,
    //         'is_available' => false
    //     ]);
    // }

    #[Test]
    public function it_returns_null_when_no_available_delivery_person()
    {
        $restaurant = \App\Models\Restaurant::factory()->create();
        $order = Order::factory()->create([
            'restaurant_id' => $restaurant->id,
            'delivery_latitude' => 40.7130,
            'delivery_longitude' => -74.0062,
        ]);

        // Create only unavailable delivery men
        DeliveryMen::factory()->create([
            'latitude' => 40.7125,
            'longitude' => -74.0058,
            'is_available' => false
        ]);

        $assignedDeliveryMan = $this->deliveryAssignmentService->assignDeliveryPerson($order);

        $this->assertNull($assignedDeliveryMan);

        // Order should remain unassigned
        $order->refresh();
        $this->assertNull($order->delivery_men_id);
        $this->assertEquals('pending', $order->status);
    }

    #[Test]
    public function it_reassigns_delivery_person_when_rejected()
    {
        $restaurant = \App\Models\Restaurant::factory()->create();

        $originalDeliveryMan = DeliveryMen::factory()->create([
            'latitude' => 40.7125,
            'longitude' => -74.0058,
            'is_available' => false
        ]);

        $order = Order::factory()->create([
            'restaurant_id' => $restaurant->id,
            'delivery_latitude' => 40.7130,
            'delivery_longitude' => -74.0062,
            'delivery_men_id' => $originalDeliveryMan->id,
            'status' => 'assigned'
        ]);

        // Create another available delivery person
        $newDeliveryMan = DeliveryMen::factory()->create([
            'latitude' => 40.7126,
            'longitude' => -74.0059,
            'is_available' => true
        ]);

        $reassignedDeliveryMan = $this->deliveryAssignmentService->reassignDeliveryPerson($order);

        $this->assertNotNull($reassignedDeliveryMan);
        $this->assertEquals($newDeliveryMan->id, $reassignedDeliveryMan->id);

        // Order should be reassigned
        $order->refresh();
        $this->assertEquals($newDeliveryMan->id, $order->delivery_men_id);
        $this->assertEquals('assigned', $order->status);

        // Original delivery man should be available again
        $this->assertDatabaseHas('delivery_mens', [
            'id' => $originalDeliveryMan->id,
            'is_available' => true
        ]);

        // New delivery man should be unavailable
        $this->assertDatabaseHas('delivery_mens', [
            'id' => $newDeliveryMan->id,
            'is_available' => false
        ]);
    }
}
