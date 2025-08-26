<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DeliveryZone;
use App\Models\Restaurant;

class DeliveryZoneFactory extends Factory
{
    protected $model = DeliveryZone::class;

    public function definition()
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'type' => 'radius',
            'coordinates' => null,
            'radius' => $this->faker->numberBetween(1000, 10000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function radius()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'radius',
                'coordinates' => null,
                'radius' => $this->faker->numberBetween(1000, 10000),
            ];
        });
    }

    public function polygon()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'polygon',
                'coordinates' => json_encode([
                    ['lat' => $this->faker->latitude, 'lng' => $this->faker->longitude],
                    ['lat' => $this->faker->latitude, 'lng' => $this->faker->longitude],
                    ['lat' => $this->faker->latitude, 'lng' => $this->faker->longitude],
                    ['lat' => $this->faker->latitude, 'lng' => $this->faker->longitude],
                ]),
                'radius' => null,
            ];
        });
    }
}
