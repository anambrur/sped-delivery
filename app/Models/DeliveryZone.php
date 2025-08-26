<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'type',
        'coordinates',
        'radius',
        'center'
    ];

    protected $casts = [
        'radius' => 'float',
        'center' => 'array',
        'coordinates' => 'array',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Check if a point is inside the delivery zone
     */
    public function containsPoint($latitude, $longitude)
    {
        if ($this->type === 'radius') {
            return $this->isPointInRadius($latitude, $longitude);
        } else {
            return $this->isPointInPolygon($latitude, $longitude);
        }
    }

    /**
     * Check if point is within radius
     */
    private function isPointInRadius($latitude, $longitude)
    {
        $center = $this->center;
        $distance = $this->calculateDistance(
            $center['latitude'],
            $center['longitude'],
            $latitude,
            $longitude
        );

        return $distance <= $this->radius;
    }

    /**
     * Check if point is inside polygon using ray casting algorithm
     */
    private function isPointInPolygon($latitude, $longitude)
    {
        $polygon = json_decode($this->coordinates, true);
        $inside = false;
        $x = $longitude;
        $y = $latitude;

        for ($i = 0, $j = count($polygon) - 1; $i < count($polygon); $j = $i++) {
            $xi = $polygon[$i]['lng'];
            $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lng'];
            $yj = $polygon[$j]['lat'];

            $intersect = (($yi > $y) != ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
