<?php

namespace App\Http\Controllers\Api;

use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DeliveryZoneController extends Controller
{
    public function index()
    {
        try {
            $zones = DeliveryZone::with('restaurant')->get();

            return response()->json([
                'success' => true,
                'data' => $zones,
                'count' => $zones->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch delivery zones',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'restaurant_id' => 'required|exists:restaurants,id',
                'type' => 'required|in:polygon,radius',
                'coordinates' => 'required_if:type,polygon|array|min:3',
                'coordinates.*.lat' => 'required_if:type,polygon|numeric|between:-90,90',
                'coordinates.*.lng' => 'required_if:type,polygon|numeric|between:-180,180',
                'radius' => 'required_if:type,radius|numeric|min:1|max:50000', // in meters (max 50km)
            ], [
                'restaurant_id.required' => 'Restaurant ID is required.',
                'restaurant_id.exists' => 'The selected restaurant does not exist.',
                'type.required' => 'Zone type is required.',
                'type.in' => 'Zone type must be either polygon or radius.',
                'coordinates.required_if' => 'Coordinates are required for polygon zones.',
                'coordinates.min' => 'Polygon must have at least 3 points.',
                'coordinates.*.lat.required_if' => 'Latitude is required for each coordinate.',
                'coordinates.*.lat.between' => 'Latitude must be between -90 and 90 degrees.',
                'coordinates.*.lng.required_if' => 'Longitude is required for each coordinate.',
                'coordinates.*.lng.between' => 'Longitude must be between -180 and 180 degrees.',
                'radius.required_if' => 'Radius is required for radius zones.',
                'radius.min' => 'Radius must be at least 1 meter.',
                'radius.max' => 'Radius cannot exceed 50000 meters (50km).',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create the delivery zone
            $zone = DeliveryZone::create([
                'restaurant_id' => $request->restaurant_id,
                'type' => $request->type,
                'coordinates' => $request->type === 'polygon' ? $request->coordinates : null,
                'radius' => $request->type === 'radius' ? $request->radius : null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery zone created successfully',
                'data' => $zone->load('restaurant')
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => config('app.debug') ? $e->getMessage() : 'Database operation failed'
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $zone = DeliveryZone::with('restaurant')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $zone
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery zone not found',
                'error' => 'The requested delivery zone does not exist'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch delivery zone',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $zone = DeliveryZone::findOrFail($id);

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'restaurant_id' => 'sometimes|required|exists:restaurants,id',
                'type' => 'sometimes|required|in:polygon,radius',
                'coordinates' => 'required_if:type,polygon|array|min:3',
                'coordinates.*.lat' => 'required_if:type,polygon|numeric|between:-90,90',
                'coordinates.*.lng' => 'required_if:type,polygon|numeric|between:-180,180',
                'radius' => 'required_if:type,radius|numeric|min:1|max:50000',
            ], [
                'restaurant_id.exists' => 'The selected restaurant does not exist.',
                'type.in' => 'Zone type must be either polygon or radius.',
                'coordinates.required_if' => 'Coordinates are required for polygon zones.',
                'coordinates.min' => 'Polygon must have at least 3 points.',
                'coordinates.*.lat.required_if' => 'Latitude is required for each coordinate.',
                'coordinates.*.lat.between' => 'Latitude must be between -90 and 90 degrees.',
                'coordinates.*.lng.required_if' => 'Longitude is required for each coordinate.',
                'coordinates.*.lng.between' => 'Longitude must be between -180 and 180 degrees.',
                'radius.required_if' => 'Radius is required for radius zones.',
                'radius.min' => 'Radius must be at least 1 meter.',
                'radius.max' => 'Radius cannot exceed 50000 meters (50km).',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $updateData = $validator->validated();

            // Handle type-specific data
            if (isset($updateData['type'])) {
                if ($updateData['type'] === 'polygon') {
                    // For polygon type, remove radius from validation since it's not required
                    $updateData['radius'] = null;
                } else {
                    // For radius type, remove coordinates from validation since it's not required
                    $updateData['coordinates'] = null;
                }
            }

            $zone->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery zone updated successfully',
                'data' => $zone->fresh()->load('restaurant')
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery zone not found',
                'error' => 'The requested delivery zone does not exist'
            ], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => config('app.debug') ? $e->getMessage() : 'Database operation failed'
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $zone = DeliveryZone::findOrFail($id);

            // Check if the delivery zone is associated with any orders
            // You might want to add this check if you have order-zone relationships
            // if ($zone->orders()->count() > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Cannot delete delivery zone with associated orders'
            //     ], 422);
            // }

            $zone->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery zone deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery zone not found',
                'error' => 'The requested delivery zone does not exist'
            ], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => config('app.debug') ? $e->getMessage() : 'Database operation failed'
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getByRestaurant($restaurantId)
    {
        try {
            $zones = DeliveryZone::where('restaurant_id', $restaurantId)
                ->with('restaurant')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $zones,
                'count' => $zones->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch delivery zones for restaurant',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function validateAddress(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'restaurant_id' => 'required|exists:restaurants,id',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $restaurantId = $request->restaurant_id;
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            $zones = DeliveryZone::where('restaurant_id', $restaurantId)->get();
            $isWithinZone = false;

            foreach ($zones as $zone) {
                if ($zone->type === 'radius') {
                    // Implement radius check logic
                    $distance = $this->calculateDistance(
                        $latitude,
                        $longitude,
                        $zone->restaurant->latitude,
                        $zone->restaurant->longitude
                    );

                    if ($distance <= $zone->radius) {
                        $isWithinZone = true;
                        break;
                    }
                } else {
                    // Implement polygon check logic
                    if ($this->isPointInPolygon($latitude, $longitude, $zone->coordinates)) {
                        $isWithinZone = true;
                        break;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'is_within_delivery_zone' => $isWithinZone,
                'restaurant_id' => $restaurantId,
                'coordinates' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate address',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    // Helper methods for geo calculations
    private function calculateDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    // private function isPointInPolygon($latitude, $longitude, $polygon): bool
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
