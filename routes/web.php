<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Restaurant routes with delivery zone management
    // Restaurant routes with delivery zone management
    Route::resource('restaurants', RestaurantController::class);
    Route::get('restaurants/{id}/delivery-zones', [RestaurantController::class, 'deliveryZones'])->name('restaurants.delivery-zones');
    Route::post('restaurants/{id}/save-delivery-zone', [RestaurantController::class, 'saveDeliveryZone'])->name('restaurants.save-delivery-zone');
    Route::delete('restaurants/{restaurantId}/delete-delivery-zone/{zoneId}', [RestaurantController::class, 'deleteDeliveryZone'])->name('restaurants.delete-delivery-zone');
    Route::post('restaurants/{id}/test-delivery-point', [RestaurantController::class, 'testDeliveryPoint'])->name('restaurants.test-delivery-point');
});

require __DIR__ . '/auth.php';
