<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\Restaurant;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'delivery_men_id' => null,
            'customer_name' => $this->faker->name,
            'customer_phone' => $this->faker->phoneNumber,
            'delivery_address' => $this->faker->address,
            'delivery_latitude' => $this->faker->latitude,
            'delivery_longitude' => $this->faker->longitude,
            'order_items' => json_encode([
                [
                    'name' => $this->faker->word,
                    'quantity' => $this->faker->numberBetween(1, 5),
                    'price' => $this->faker->randomFloat(2, 5, 50)
                ]
            ]),
            'total_amount' => $this->faker->randomFloat(2, 10, 100),
            'notes' => $this->faker->optional()->sentence,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
