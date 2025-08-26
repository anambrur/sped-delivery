<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\DeliveryZone;
use Illuminate\Support\Facades\Log;

class DeliveryZoneService
{
    public function validateDeliveryLocation(Restaurant $restaurant, $latitude, $longitude): bool
    {
        try {
            $zones = DeliveryZone::where('restaurant_id', $restaurant->id)->get();

            foreach ($zones as $zone) {
                if ($zone->type === 'radius') {
                    $distance = $this->calculateDistance(
                        $latitude,
                        $longitude,
                        $restaurant->latitude,
                        $restaurant->longitude
                    );

                    if ($distance <= $zone->radius) {
                        return true;
                    }
                } else {
                    if ($this->isPointInPolygon($latitude, $longitude, $zone->coordinates)) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Delivery zone validation error: ' . $e->getMessage());
            return false;
        }
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function isPointInPolygon($latitude, $longitude, $polygonCoordinates)
    {
        // If coordinates is still a string, decode it
        if (is_string($polygonCoordinates)) {
            $polygon = json_decode($polygonCoordinates, true);
        } else {
            $polygon = $polygonCoordinates;
        }

        // Ensure we have a valid polygon array
        if (!is_array($polygon) || count($polygon) < 3) {
            return false;
        }

        $inside = false;
        $x = $longitude;
        $y = $latitude;

        for ($i = 0, $j = count($polygon) - 1; $i < count($polygon); $j = $i++) {
            $xi = $polygon[$i]['lng'] ?? $polygon[$i]['longitude'] ?? 0;
            $yi = $polygon[$i]['lat'] ?? $polygon[$i]['latitude'] ?? 0;
            $xj = $polygon[$j]['lng'] ?? $polygon[$j]['longitude'] ?? 0;
            $yj = $polygon[$j]['lat'] ?? $polygon[$j]['latitude'] ?? 0;

            $intersect = (($yi > $y) != ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
