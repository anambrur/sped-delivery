<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\DeliveryZone;
use App\Services\DeliveryZoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class DeliveryZoneServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $deliveryZoneService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deliveryZoneService = new DeliveryZoneService();
    }

    #[Test]
    public function it_validates_point_within_radius_zone()
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

        // Point within 5km radius
        $isValid = $this->deliveryZoneService->validateDeliveryLocation(
            $restaurant,
            40.7130, // Nearby point
            -74.0062
        );

        $this->assertTrue($isValid);
    }

    #[Test]
    public function it_validates_point_outside_radius_zone()
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

        // Point outside 1km radius
        $isValid = $this->deliveryZoneService->validateDeliveryLocation(
            $restaurant,
            41.0000, // Far away point
            -74.5000
        );

        $this->assertFalse($isValid);
    }

    #[Test]
    public function it_validates_point_within_polygon_zone()
    {
        $restaurant = Restaurant::factory()->create([
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        DeliveryZone::factory()->create([
            'restaurant_id' => $restaurant->id,
            'type' => 'polygon',
            'coordinates' => [
                ['lat' => 40.7130, 'lng' => -74.0065],
                ['lat' => 40.7130, 'lng' => -74.0055],
                ['lat' => 40.7120, 'lng' => -74.0055],
                ['lat' => 40.7120, 'lng' => -74.0065],
            ]
        ]);

        // Point inside the polygon
        $isValid = $this->deliveryZoneService->validateDeliveryLocation(
            $restaurant,
            40.7125, // Inside the polygon
            -74.0060
        );

        $this->assertTrue($isValid);
    }

    #[Test]
    public function it_calculates_distance_correctly()
    {
        $service = new \ReflectionClass(DeliveryZoneService::class);
        $method = $service->getMethod('calculateDistance');
        $method->setAccessible(true);

        $deliveryZoneService = new DeliveryZoneService();

        // Distance between two nearby points (should be small)
        $distance = $method->invokeArgs($deliveryZoneService, [
            40.7128,
            -74.0060, // NYC
            40.7130,
            -74.0062  // Very close to NYC
        ]);

        $this->assertLessThan(1000, $distance); // Should be less than 1km

        // Distance between two far points (should be large)
        $distance = $method->invokeArgs($deliveryZoneService, [
            40.7128,
            -74.0060, // NYC
            34.0522,
            -118.2437 // LA
        ]);

        $this->assertGreaterThan(3000000, $distance); // Should be more than 3000km
    }
}
