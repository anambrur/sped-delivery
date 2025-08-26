<?php

namespace App\Http\Controllers\Api;

use App\Models\DeliveryMen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DeliveryMenController extends Controller
{
    public function index()
    {
        try {
            $deliveryMen = DeliveryMen::all();

            return response()->json([
                'success' => true,
                'data' => $deliveryMen,
                'count' => $deliveryMen->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch delivery personnel',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'is_available' => 'sometimes|boolean',
                'phone' => 'sometimes|string|max:20',
                'vehicle_type' => 'sometimes|string|max:50',
                'capacity' => 'sometimes|numeric|min:1'
            ], [
                'name.required' => 'Name is required.',
                'name.max' => 'Name may not be greater than 255 characters.',
                'latitude.required' => 'Latitude is required.',
                'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
                'longitude.required' => 'Longitude is required.',
                'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
                'phone.max' => 'Phone number may not be greater than 20 characters.',
                'capacity.min' => 'Capacity must be at least 1.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create the delivery person
            $deliveryMen = DeliveryMen::create($validator->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery person created successfully',
                'data' => $deliveryMen
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
            $deliveryMen = DeliveryMen::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $deliveryMen
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery person not found',
                'error' => 'The requested delivery person does not exist'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch delivery person',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $deliveryMen = DeliveryMen::findOrFail($id);

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'latitude' => 'sometimes|required|numeric|between:-90,90',
                'longitude' => 'sometimes|required|numeric|between:-180,180',
                'is_available' => 'sometimes|boolean',
                'phone' => 'sometimes|string|max:20',
                'vehicle_type' => 'sometimes|string|max:50',
                'capacity' => 'sometimes|numeric|min:1'
            ], [
                'name.required' => 'Name is required.',
                'name.max' => 'Name may not be greater than 255 characters.',
                'latitude.required' => 'Latitude is required.',
                'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
                'longitude.required' => 'Longitude is required.',
                'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
                'phone.max' => 'Phone number may not be greater than 20 characters.',
                'capacity.min' => 'Capacity must be at least 1.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $deliveryMen->update($validator->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery person updated successfully',
                'data' => $deliveryMen->fresh()
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery person not found',
                'error' => 'The requested delivery person does not exist'
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

            $deliveryMen = DeliveryMen::findOrFail($id);

            // Check if delivery person has assigned orders
            if ($deliveryMen->orders()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete delivery person with assigned orders'
                ], 422);
            }

            $deliveryMen->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery person deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery person not found',
                'error' => 'The requested delivery person does not exist'
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

    public function updateAvailability(Request $request, $id)
    {
        try {
            $deliveryMen = DeliveryMen::findOrFail($id);

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'is_available' => 'required|boolean'
            ], [
                'is_available.required' => 'Availability status is required.',
                'is_available.boolean' => 'Availability must be true or false.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $deliveryMen->update($validator->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Availability updated successfully',
                'data' => $deliveryMen
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery person not found',
                'error' => 'The requested delivery person does not exist'
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

    public function getAvailable()
    {
        try {
            $availableDeliveryMen = DeliveryMen::where('is_available', true)->get();

            return response()->json([
                'success' => true,
                'data' => $availableDeliveryMen,
                'count' => $availableDeliveryMen->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available delivery personnel',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getNearby(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'sometimes|numeric|min:1|max:100', // in kilometers
                'only_available' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $radius = $request->input('radius', 10); // default 10km
            $onlyAvailable = $request->input('only_available', true);

            // Use different approach for SQLite vs other databases
            if (config('database.default') === 'sqlite') {
                // SQLite fallback - calculate distances in PHP
                $query = DeliveryMen::query();

                if ($onlyAvailable) {
                    $query->where('is_available', true);
                }

                $deliveryMen = $query->get()->filter(function ($deliveryMan) use ($latitude, $longitude, $radius) {
                    $distance = $this->calculateHaversineDistance(
                        $latitude,
                        $longitude,
                        $deliveryMan->latitude,
                        $deliveryMan->longitude
                    );
                    $deliveryMan->distance = $distance;
                    return $distance <= $radius;
                })->sortBy('distance')->values();
            } else {
                // MySQL/PostgreSQL - use SQL calculation
                $deliveryMen = DeliveryMen::selectRaw(
                    "*, 
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude)))) AS distance",
                    [$latitude, $longitude, $latitude]
                );

                if ($onlyAvailable) {
                    $deliveryMen->where('is_available', true);
                }

                $deliveryMen = $deliveryMen->having('distance', '<=', $radius)
                    ->orderBy('distance')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $deliveryMen,
                'count' => $deliveryMen->count(),
                'search_center' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ],
                'radius_km' => $radius,
                'only_available' => $onlyAvailable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to find nearby delivery personnel',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
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
}
