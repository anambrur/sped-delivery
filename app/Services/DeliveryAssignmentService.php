<?php

namespace App\Services;

use App\Models\Order;
use App\Models\DeliveryMen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewOrderAssignment;

class DeliveryAssignmentService
{
    public function assignDeliveryPerson(Order $order): ?DeliveryMen
    {
        try {
            DB::beginTransaction();

            $deliveryPerson = $this->findAvailableDeliveryPerson(
                $order->delivery_latitude,
                $order->delivery_longitude
            );

            if ($deliveryPerson) {
                $order->delivery_men_id = $deliveryPerson->id;
                $order->status = 'assigned';
                $order->save();

                // Mark delivery person as unavailable
                $deliveryPerson->is_available = false;
                $deliveryPerson->save();

                // Send notification using Laravel's built-in system
                $deliveryPerson->notify(new NewOrderAssignment($order));
            }

            DB::commit();

            return $deliveryPerson;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery assignment error: ' . $e->getMessage());
            return null;
        }
    }

    private function findAvailableDeliveryPerson($latitude, $longitude): ?DeliveryMen
    {
        // Use SQL-based approach for MySQL/PostgreSQL, PHP-based for SQLite
        if (config('database.default') !== 'sqlite') {
            return DeliveryMen::selectRaw(
                "*, 
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + 
            sin(radians(?)) * sin(radians(latitude)))) AS distance",
                [$latitude, $longitude, $latitude]
            )
                ->where('is_available', true)
                ->having('distance', '<=', 10)
                ->orderBy('distance')
                ->first();
        } else {
            // SQLite fallback - use PHP calculation
            $availableDeliveryMen = DeliveryMen::where('is_available', true)->get();

            if ($availableDeliveryMen->isEmpty()) {
                return null;
            }

            $nearestDeliveryMan = null;
            $shortestDistance = PHP_FLOAT_MAX;

            foreach ($availableDeliveryMen as $deliveryMan) {
                $distance = $this->calculateHaversineDistance(
                    $latitude,
                    $longitude,
                    $deliveryMan->latitude,
                    $deliveryMan->longitude
                );

                if ($distance <= 10 && $distance < $shortestDistance) {
                    $shortestDistance = $distance;
                    $nearestDeliveryMan = $deliveryMan;
                }
            }

            return $nearestDeliveryMan;
        }
    }

    private function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function reassignDeliveryPerson(Order $order): ?DeliveryMen
    {
        try {
            DB::beginTransaction();

            // Free up the previous delivery person
            if ($order->delivery_men_id) {
                $previousDeliveryPerson = DeliveryMen::find($order->delivery_men_id);
                if ($previousDeliveryPerson) {
                    $previousDeliveryPerson->is_available = true;
                    $previousDeliveryPerson->save();
                }
            }

            // Find a new delivery person
            $newDeliveryPerson = $this->findAvailableDeliveryPerson(
                $order->delivery_latitude,
                $order->delivery_longitude
            );

            if ($newDeliveryPerson) {
                $order->delivery_men_id = $newDeliveryPerson->id;
                $order->status = 'assigned';
                $order->save();

                // Mark new delivery person as unavailable
                $newDeliveryPerson->is_available = false;
                $newDeliveryPerson->save();
            } else {
                $order->delivery_men_id = null;
                $order->status = 'pending';
                $order->save();
            }

            DB::commit();

            return $newDeliveryPerson;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery reassignment error: ' . $e->getMessage());
            return null;
        }
    }
}
