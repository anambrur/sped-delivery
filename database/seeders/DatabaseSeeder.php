<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Restaurant;
use App\Models\DeliveryMen;
use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'admin@gmail.com',
        //     'password' => bcrypt('12345678')
        // ]);
        $this->call(PermissionSeeder::class);

        Restaurant::factory()->count(10)->create();
        DeliveryMen::factory()->count(10)->create();

        $this->call(DeliveryZoneSeeder::class);
        $this->call(OrderSeeder::class);
    }
}
