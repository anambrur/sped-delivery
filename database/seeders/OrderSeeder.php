<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\DeliveryMen;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $restaurants = Restaurant::all();
        $deliveryMen = DeliveryMen::where('is_available', true)->get();

        $statuses = ['pending', 'assigned', 'accepted', 'in_transit', 'delivered', 'cancelled'];

        foreach ($restaurants as $restaurant) {
            // Create 3-5 orders for each restaurant
            $orderCount = rand(3, 5);

            for ($i = 0; $i < $orderCount; $i++) {
                $status = $statuses[array_rand($statuses)];

                $orderData = [
                    'restaurant_id' => $restaurant->id,
                    'delivery_men_id' => ($status !== 'pending') ? $deliveryMen->random()->id : null,
                    'customer_name' => 'Customer ' . ($i + 1),
                    'customer_phone' => '+1-555-' . sprintf('%04d', rand(1000, 9999)),
                    'delivery_address' => $this->generateRandomAddress(),
                    'delivery_latitude' => $restaurant->latitude + (rand(-50, 50) / 1000),
                    'delivery_longitude' => $restaurant->longitude + (rand(-50, 50) / 1000),
                    'order_items' => json_encode($this->generateOrderItems()),
                    'total_amount' => rand(1500, 5000) / 100, // $15.00 - $50.00
                    'notes' => rand(0, 1) ? 'Special delivery instructions' : null,
                    'status' => $status,
                ];

                Order::create($orderData);
            }
        }
    }

    private function generateRandomAddress()
    {
        $streets = ['Main St', 'Oak Ave', 'Pine Rd', 'Elm Blvd', 'Maple Ln', 'Cedar Dr'];
        $cities = ['New York', 'Brooklyn', 'Queens', 'Bronx', 'Staten Island'];

        return rand(100, 999) . ' ' . $streets[array_rand($streets)] . ', ' .
            $cities[array_rand($cities)] . ', NY ' . sprintf('%05d', rand(10000, 19999));
    }

    private function generateOrderItems()
    {
        $items = [
            ['name' => 'Margherita Pizza', 'quantity' => rand(1, 3), 'price' => 12.99],
            ['name' => 'Pepperoni Pizza', 'quantity' => rand(1, 2), 'price' => 14.99],
            ['name' => 'Vegetarian Pizza', 'quantity' => 1, 'price' => 13.99],
            ['name' => 'Cheeseburger', 'quantity' => rand(1, 2), 'price' => 8.99],
            ['name' => 'Bacon Burger', 'quantity' => 1, 'price' => 10.99],
            ['name' => 'California Roll', 'quantity' => rand(2, 4), 'price' => 5.99],
            ['name' => 'Spicy Tuna Roll', 'quantity' => rand(2, 3), 'price' => 6.99],
            ['name' => 'Beef Tacos', 'quantity' => rand(2, 4), 'price' => 3.99],
            ['name' => 'Chicken Tacos', 'quantity' => rand(2, 3), 'price' => 3.99],
            ['name' => 'Spaghetti Bolognese', 'quantity' => 1, 'price' => 11.99],
            ['name' => 'Fettuccine Alfredo', 'quantity' => 1, 'price' => 12.99],
        ];

        $selectedItems = [];
        $itemCount = rand(1, 3);

        for ($i = 0; $i < $itemCount; $i++) {
            $selectedItems[] = $items[array_rand($items)];
        }

        return $selectedItems;
    }
}
