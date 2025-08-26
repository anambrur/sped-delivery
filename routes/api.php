<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\DeliveryMenController;
use App\Http\Controllers\Api\DeliveryZoneController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Restaurant Routes
    Route::prefix('restaurants')->group(function () {
        Route::get('/', [RestaurantController::class, 'index'])->middleware('permission:restaurants.view');
        Route::post('/', [RestaurantController::class, 'store'])->middleware('permission:restaurants.create');
        Route::get('/search/{query}', [RestaurantController::class, 'search'])->middleware('permission:restaurants.view');
        Route::get('/nearby', [RestaurantController::class, 'nearby'])->middleware('permission:restaurants.view');
        Route::get('/{id}', [RestaurantController::class, 'show'])->middleware('permission:restaurants.view');
        Route::put('/{id}', [RestaurantController::class, 'update'])->middleware('permission:restaurants.edit');
        Route::patch('/{id}', [RestaurantController::class, 'update'])->middleware('permission:restaurants.edit');
        Route::delete('/{id}', [RestaurantController::class, 'destroy'])->middleware('permission:restaurants.delete');
    });

    // Delivery Zones Routes
    Route::prefix('delivery-zones')->group(function () {
        Route::get('/', [DeliveryZoneController::class, 'index'])->middleware('permission:delivery-zones.view');
        Route::post('/', [DeliveryZoneController::class, 'store'])->middleware('permission:delivery-zones.create');

        // Specific routes first
        Route::get('/restaurant/{restaurantId}', [DeliveryZoneController::class, 'getByRestaurant'])->middleware('permission:delivery-zones.view');
        Route::post('/validate-address', [DeliveryZoneController::class, 'validateAddress'])->middleware('permission:delivery-zones.view');

        // Generic routes last
        Route::get('/{id}', [DeliveryZoneController::class, 'show'])->middleware('permission:delivery-zones.view');
        Route::put('/{id}', [DeliveryZoneController::class, 'update'])->middleware('permission:delivery-zones.edit');
        Route::patch('/{id}', [DeliveryZoneController::class, 'update'])->middleware('permission:delivery-zones.edit');
        Route::delete('/{id}', [DeliveryZoneController::class, 'destroy'])->middleware('permission:delivery-zones.delete');
    });

    // Order Routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->middleware('permission:orders.view');
        Route::post('/', [OrderController::class, 'store'])->middleware('permission:orders.create');
        Route::get('/{id}', [OrderController::class, 'show'])->middleware('permission:orders.view');
        Route::put('/{id}', [OrderController::class, 'update'])->middleware('permission:orders.edit');
        Route::delete('/{id}', [OrderController::class, 'destroy'])->middleware('permission:orders.delete');
        Route::post('/{id}/accept', [OrderController::class, 'accept'])->middleware('permission:orders.edit');
        Route::post('/{id}/reject', [OrderController::class, 'reject'])->middleware('permission:orders.edit');
        Route::put('/{id}/status', [OrderController::class, 'updateStatus'])->middleware('permission:orders.edit');
    });

    // Delivery Persons Routes
    Route::prefix('delivery-men')->group(function () {
        Route::get('/', [DeliveryMenController::class, 'index'])->middleware('permission:delivery-men.view');
        Route::post('/', [DeliveryMenController::class, 'store'])->middleware('permission:delivery-men.create');
        Route::get('/available', [DeliveryMenController::class, 'getAvailable'])->middleware('permission:delivery-men.view');
        Route::get('/nearby', [DeliveryMenController::class, 'getNearby'])->middleware('permission:delivery-men.view');
        Route::get('/{id}', [DeliveryMenController::class, 'show'])->middleware('permission:delivery-men.view');
        Route::put('/{id}', [DeliveryMenController::class, 'update'])->middleware('permission:delivery-men.edit');
        Route::patch('/{id}', [DeliveryMenController::class, 'update'])->middleware('permission:delivery-men.edit');
        Route::delete('/{id}', [DeliveryMenController::class, 'destroy'])->middleware('permission:delivery-men.delete');

        Route::post('/{id}/availability', [DeliveryMenController::class, 'updateAvailability'])->middleware('permission:delivery-men.edit');
    });
});
