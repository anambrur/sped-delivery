<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryZone;
use App\Models\Restaurant;

class DeliveryZoneSeeder extends Seeder
{
    public function run()
    {
        $restaurants = Restaurant::all();

        foreach ($restaurants as $restaurant) {
            // Create radius-based delivery zone
            DeliveryZone::create([
                'restaurant_id' => $restaurant->id,
                'name' => 'Radius Zone',
                'type' => 'radius',
                'radius' => 5000, // 5km radius
                'coordinates' => null,
                'center' => json_encode([
                    'lat' => $restaurant->latitude,
                    'lng' => $restaurant->longitude
                ])
            ]);

            // Create polygon-based delivery zone for some restaurants
            if ($restaurant->id % 2 == 0) {
                DeliveryZone::create([
                    'restaurant_id' => $restaurant->id,
                    'name' => 'Polygon Zone',
                    'type' => 'polygon',
                    'coordinates' => json_encode([
                        [
                            'lat' => $restaurant->latitude + 0.01,
                            'lng' => $restaurant->longitude - 0.01
                        ],
                        [
                            'lat' => $restaurant->latitude + 0.01,
                            'lng' => $restaurant->longitude + 0.01
                        ],
                        [
                            'lat' => $restaurant->latitude - 0.01,
                            'lng' => $restaurant->longitude + 0.01
                        ],
                        [
                            'lat' => $restaurant->latitude - 0.01,
                            'lng' => $restaurant->longitude - 0.01
                        ],
                    ]),
                    'radius' => null,
                    'center' => null
                ]);
            }
        }
    }
}
