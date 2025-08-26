<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DeliveryMen;

class DeliveryMenFactory extends Factory
{
    protected $model = DeliveryMen::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'vehicle_type' => $this->faker->randomElement(['motorcycle', 'car', 'bicycle', null]),
            'capacity' => $this->faker->numberBetween(1, 10),
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
