<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\DeliveryMen;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\DeliveryZoneService;
use Illuminate\Support\Facades\Validator;
use App\Services\DeliveryAssignmentService;

class OrderController extends Controller
{
    protected $deliveryZoneService;
    protected $deliveryAssignmentService;

    public function __construct(
        DeliveryZoneService $deliveryZoneService,
        DeliveryAssignmentService $deliveryAssignmentService
    ) {
        $this->deliveryZoneService = $deliveryZoneService;
        $this->deliveryAssignmentService = $deliveryAssignmentService;
    }

    public function index()
    {
        try {
            $orders = Order::with(['restaurant', 'deliveryPerson'])->get();

            return response()->json([
                'success' => true,
                'data' => $orders,
                'count' => $orders->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
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
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'delivery_address' => 'required|string|max:500',
                'delivery_latitude' => 'required|numeric|between:-90,90',
                'delivery_longitude' => 'required|numeric|between:-180,180',
                'order_items' => 'required|array|min:1',
                'order_items.*.name' => 'required|string|max:255',
                'order_items.*.quantity' => 'required|integer|min:1',
                'order_items.*.price' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'notes' => 'sometimes|string|max:1000'
            ], [
                'restaurant_id.required' => 'Restaurant ID is required.',
                'restaurant_id.exists' => 'The selected restaurant does not exist.',
                'customer_name.required' => 'Customer name is required.',
                'customer_phone.required' => 'Customer phone is required.',
                'delivery_address.required' => 'Delivery address is required.',
                'delivery_latitude.required' => 'Delivery latitude is required.',
                'delivery_latitude.between' => 'Latitude must be between -90 and 90 degrees.',
                'delivery_longitude.required' => 'Delivery longitude is required.',
                'delivery_longitude.between' => 'Longitude must be between -180 and 180 degrees.',
                'order_items.required' => 'Order items are required.',
                'order_items.min' => 'At least one order item is required.',
                'total_amount.required' => 'Total amount is required.',
                'total_amount.min' => 'Total amount must be at least 0.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $restaurant = Restaurant::findOrFail($request->restaurant_id);

            // Validate delivery location
            $isValidLocation = $this->deliveryZoneService->validateDeliveryLocation(
                $restaurant,
                $request->delivery_latitude,
                $request->delivery_longitude
            );

            if (!$isValidLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delivery location is not within the restaurant\'s delivery zone'
                ], 422);
            }

            // Create the order
            $order = Order::create($validator->validated());

            // Assign a delivery person
            $deliveryPerson = $this->deliveryAssignmentService->assignDeliveryPerson($order);

            DB::commit();

            $response = [
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['restaurant', 'deliveryPerson'])
            ];

            if ($deliveryPerson) {
                $response['delivery_person'] = $deliveryPerson;
            } else {
                $response['message'] = 'Order created but no delivery person available';
            }

            return response()->json($response, 201);
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
            $order = Order::with(['restaurant', 'deliveryPerson'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => 'The requested order does not exist'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'customer_name' => 'sometimes|required|string|max:255',
                'customer_phone' => 'sometimes|required|string|max:20',
                'delivery_address' => 'sometimes|required|string|max:500',
                'delivery_latitude' => 'sometimes|required|numeric|between:-90,90',
                'delivery_longitude' => 'sometimes|required|numeric|between:-180,180',
                'status' => 'sometimes|required|in:pending,assigned,accepted,in_transit,delivered,cancelled',
                'notes' => 'sometimes|string|max:1000'
            ], [
                'customer_name.required' => 'Customer name is required.',
                'customer_phone.required' => 'Customer phone is required.',
                'delivery_address.required' => 'Delivery address is required.',
                'delivery_latitude.required' => 'Delivery latitude is required.',
                'delivery_latitude.between' => 'Latitude must be between -90 and 90 degrees.',
                'delivery_longitude.required' => 'Delivery longitude is required.',
                'delivery_longitude.between' => 'Longitude must be between -180 and 180 degrees.',
                'status.required' => 'Status is required.',
                'status.in' => 'Status must be one of: pending, assigned, accepted, in_transit, delivered, cancelled.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // If delivery location changed, validate it
            if ($request->has('delivery_latitude') || $request->has('delivery_longitude')) {
                $latitude = $request->delivery_latitude ?? $order->delivery_latitude;
                $longitude = $request->delivery_longitude ?? $order->delivery_longitude;

                $isValidLocation = $this->deliveryZoneService->validateDeliveryLocation(
                    $order->restaurant,
                    $latitude,
                    $longitude
                );

                if (!$isValidLocation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Delivery location is not within the restaurant\'s delivery zone'
                    ], 422);
                }
            }

            $order->update($validator->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order->fresh()->load(['restaurant', 'deliveryPerson'])
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => 'The requested order does not exist'
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

            $order = Order::findOrFail($id);

            // Check if order can be deleted based on status
            if (!in_array($order->status, ['pending', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete order with status: ' . $order->status
                ], 422);
            }

            // Free up delivery person if assigned
            if ($order->delivery_men_id) {
                $deliveryPerson = DeliveryMen::find($order->delivery_men_id);
                if ($deliveryPerson) {
                    $deliveryPerson->is_available = true;
                    $deliveryPerson->save();
                }
            }

            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => 'The requested order does not exist'
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

    public function accept($id)
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($id);

            // Check if the order is already assigned
            if ($order->delivery_men_id && $order->status === 'assigned') {
                $order->status = 'accepted';
                $order->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Order accepted successfully',
                    'data' => $order->load(['restaurant', 'deliveryPerson'])
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Order cannot be accepted. It must be in assigned status with a delivery person.'
            ], 400);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => 'The requested order does not exist'
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

    public function reject($id)
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($id);

            // Check if the order is assigned to a delivery person
            if ($order->delivery_men_id && $order->status === 'assigned') {
                $newDeliveryPerson = $this->deliveryAssignmentService->reassignDeliveryPerson($order);

                DB::commit();

                $response = [
                    'success' => true,
                    'message' => 'Order rejected, reassigning to another delivery person',
                    'data' => $order->fresh()->load(['restaurant', 'deliveryPerson'])
                ];

                if ($newDeliveryPerson) {
                    $response['new_delivery_person'] = $newDeliveryPerson;
                } else {
                    $response['message'] = 'Order rejected but no alternative delivery person available';
                }

                return response()->json($response);
            }

            return response()->json([
                'success' => false,
                'message' => 'Order cannot be rejected. It must be in assigned status with a delivery person.'
            ], 400);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => 'The requested order does not exist'
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

    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,assigned,accepted,in_transit,delivered,cancelled'
            ], [
                'status.required' => 'Status is required.',
                'status.in' => 'Status must be one of: pending, assigned, accepted, in_transit, delivered, cancelled.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $order = Order::findOrFail($id);
            $order->status = $request->status;
            $order->save();

            // If order is delivered or cancelled, free up the delivery person
            if (in_array($request->status, ['delivered', 'cancelled']) && $order->delivery_men_id) {
                $deliveryPerson = DeliveryMen::find($order->delivery_men_id);
                if ($deliveryPerson) {
                    $deliveryPerson->is_available = true;
                    $deliveryPerson->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order->fresh()->load(['restaurant', 'deliveryPerson'])
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => 'The requested order does not exist'
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
}
