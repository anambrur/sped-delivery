<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RestaurantController extends Controller
{
    public function index()
    {
        $restaurants = Restaurant::withCount(['deliveryZones', 'orders'])
            ->orderBy('name')
            ->get();

        return view('restaurants.index', compact('restaurants'));
    }

    public function create()
    {
        return view('restaurants.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:restaurants,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $restaurant = Restaurant::create($request->all());
            DB::commit();

            return redirect()->route('restaurants.delivery-zones', $restaurant->id)
                ->with('success', 'Restaurant created successfully! Now define delivery zones.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating restaurant: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $restaurant = Restaurant::with(['deliveryZones', 'orders'])->findOrFail($id);
        return view('restaurants.show', compact('restaurant'));
    }

    public function edit($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        return view('restaurants.edit', compact('restaurant'));
    }

    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:restaurants,email,' . $id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $restaurant->update($request->all());
            return redirect()->route('restaurants.index')
                ->with('success', 'Restaurant updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating restaurant: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);

        if ($restaurant->deliveryZones()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete restaurant with existing delivery zones');
        }

        if ($restaurant->orders()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete restaurant with existing orders');
        }

        try {
            $restaurant->delete();
            return redirect()->route('restaurants.index')
                ->with('success', 'Restaurant deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting restaurant: ' . $e->getMessage());
        }
    }

    public function deliveryZones($id)
    {
        $restaurant = Restaurant::with('deliveryZones')->findOrFail($id);
        return view('restaurants.delivery-zones', compact('restaurant'));
    }

    public function saveDeliveryZone(Request $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:polygon,radius',
            'coordinates' => 'required_if:type,polygon|json',
            'radius' => 'required_if:type,radius|numeric|min:0.1',
            'center' => 'required_if:type,radius|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $zoneData = [
                'restaurant_id' => $restaurant->id,
                'name' => $request->name,
                'type' => $request->type,
            ];

            if ($request->type === 'polygon') {
                $zoneData['coordinates'] = $request->coordinates;
            } else {
                $zoneData['radius'] = $request->radius;
                $zoneData['center'] = $request->center;
            }

            DeliveryZone::create($zoneData);

            return response()->json([
                'success' => true,
                'message' => 'Delivery zone saved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving delivery zone: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteDeliveryZone($restaurantId, $zoneId)
    {
        $zone = DeliveryZone::where('restaurant_id', $restaurantId)
            ->where('id', $zoneId)
            ->firstOrFail();

        try {
            $zone->delete();
            return redirect()->back()
                ->with('success', 'Delivery zone deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting delivery zone: ' . $e->getMessage());
        }
    }

    public function testDeliveryPoint(Request $request, $id)
    {
        $restaurant = Restaurant::with('deliveryZones')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $isWithinZone = false;
        $matchingZones = [];

        foreach ($restaurant->deliveryZones as $zone) {
            if ($zone->containsPoint($latitude, $longitude)) {
                $isWithinZone = true;
                $matchingZones[] = $zone->name;
            }
        }

        return response()->json([
            'success' => true,
            'within_zone' => $isWithinZone,
            'matching_zones' => $matchingZones,
            'test_point' => [
                'latitude' => $latitude,
                'longitude' => $longitude
            ]
        ]);
    }
}
