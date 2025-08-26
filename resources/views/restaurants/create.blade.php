@extends('layouts.main-layout')

@section('title', 'Create Restaurant with Delivery Zones')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <style>
        #previewMap {
            z-index: 1;
        }

        .leaflet-top.leaflet-left .leaflet-control {
            margin-top: 60px;
        }
    </style>
@endsection

@section('content')
    <!-- ===== Main Content Start ===== -->
    <main class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="#"
                            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                            <i class="fas fa-home mr-2"></i>
                            Home
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Create
                                Restaurant</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Error Messages -->
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-5 rounded-md shadow-sm hidden" id="errorAlert">
            <div class="flex justify-between items-center">
                <div>
                    <p class="font-bold">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc list-inside" id="errorList"></ul>
                </div>
                <button type="button" class="text-red-700"
                    onclick="document.getElementById('errorAlert').classList.add('hidden')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Form Container -->
        <div
            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 sm:px-6 sm:py-5 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                    Create New Restaurant
                </h3>
            </div>

            <div class="p-5 sm:p-6 bg-white dark:bg-gray-800">
                <form action="{{ route('restaurants.store') }}" method="POST" id="restaurantForm">
                    @csrf
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Left Column - Restaurant Details -->
                        <div class="space-y-6">
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Restaurant Name *
                                </label>
                                <input type="text" id="name" name="name"
                                    class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 
                                    bg-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                    dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white 
                                    dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Enter restaurant name" required>
                            </div>

                            <div>
                                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Email Address *
                                </label>
                                <input type="email" id="email" name="email"
                                    class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 
                                    bg-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                    dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white 
                                    dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Enter email address" required>
                            </div>

                            <div>
                                <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Phone Number *
                                </label>
                                <input type="text" id="phone" name="phone"
                                    class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 
                                    bg-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                    dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white 
                                    dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Enter phone number" required>
                            </div>

                            <div>
                                <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Address *
                                </label>
                                <textarea id="address" name="address" rows="3"
                                    class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 
                                    bg-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                    dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white 
                                    dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Enter full address" required></textarea>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="latitude"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Latitude *
                                    </label>
                                    <input type="number" step="any" id="latitude" name="latitude"
                                        class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 
                                        bg-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white 
                                        dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="e.g., 40.7128" required>
                                </div>

                                <div>
                                    <label for="longitude"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Longitude *
                                    </label>
                                    <input type="number" step="any" id="longitude" name="longitude"
                                        class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 
                                        bg-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white 
                                        dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="e.g., -74.0060" required>
                                </div>
                            </div>

                            <div class="pt-4 flex items-center">
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2.5 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <i class="fas fa-save mr-2"></i> Create Restaurant
                                </button>
                                <a href="#"
                                    class="inline-flex items-center px-4 py-2.5 ml-3 bg-gray-200 border border-transparent rounded-md font-semibold text-gray-800 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                                    <i class="fas fa-times mr-2"></i> Cancel
                                </a>
                            </div>
                        </div>

                        <!-- Right Column - Map for Delivery Zones -->
                        <div class="space-y-6">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Delivery Zone Setup
                                </label>
                                <div
                                    class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:bg-gray-700 dark:border-gray-600">
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                                        After creating the restaurant, you'll be able to define delivery zones on the next
                                        screen.
                                    </p>

                                    <div id="previewMap"
                                        class="w-full h-64 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600">
                                    </div>

                                    <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                                        <p class="flex items-center mb-1"><i class="fas fa-info-circle mr-2"></i> You can
                                            define both polygon areas and radius-based delivery zones.</p>
                                        <p class="flex items-center"><i class="fas fa-info-circle mr-2"></i> Use the map
                                            controls to draw your delivery areas.</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                                <h4 class="font-semibold text-blue-800 dark:text-blue-300 flex items-center">
                                    <i class="fas fa-lightbulb mr-2"></i> Pro Tip
                                </h4>
                                <p class="text-sm text-blue-700 dark:text-blue-300 mt-2">
                                    For accurate delivery zone setup, make sure the latitude and longitude coordinates are
                                    precise.
                                    You can use services like Google Maps to find the exact coordinates of your restaurant.
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

@endsection

@section('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize preview map
            const defaultLat = 40.7128;
            const defaultLng = -74.0060;

            const previewMap = L.map('previewMap').setView([defaultLat, defaultLng], 13);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(previewMap);

            // Add marker for restaurant location
            let restaurantMarker = L.marker([defaultLat, defaultLng], {
                draggable: false
            }).addTo(previewMap);

            restaurantMarker.bindPopup('Restaurant Location').openPopup();

            // Update marker position when coordinates change
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');

            function updateMarkerPosition() {
                const lat = parseFloat(latInput.value) || defaultLat;
                const lng = parseFloat(lngInput.value) || defaultLng;

                restaurantMarker.setLatLng([lat, lng]);
                previewMap.setView([lat, lng], 13);
                restaurantMarker.bindPopup('Restaurant Location').openPopup();
            }

            latInput.addEventListener('change', updateMarkerPosition);
            lngInput.addEventListener('change', updateMarkerPosition);

            // Form validation
            document.getElementById('restaurantForm').addEventListener('submit', function(e) {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                const errors = [];

                if (isNaN(lat) || lat < -90 || lat > 90) {
                    errors.push('Please enter a valid latitude between -90 and 90.');
                }

                if (isNaN(lng) || lng < -180 || lng > 180) {
                    errors.push('Please enter a valid longitude between -180 and 180.');
                }

                if (errors.length > 0) {
                    e.preventDefault(); // Only prevent if there are errors

                    const errorList = document.getElementById('errorList');
                    errorList.innerHTML = '';

                    errors.forEach(error => {
                        const li = document.createElement('li');
                        li.textContent = error;
                        errorList.appendChild(li);
                    });

                    document.getElementById('errorAlert').classList.remove('hidden');
                }
                // If no errors, the form will submit normally
            });

            // Try to get user's location for better default coordinates
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Only set default values if inputs are empty
                        if (!latInput.value && !lngInput.value) {
                            latInput.value = position.coords.latitude.toFixed(6);
                            lngInput.value = position.coords.longitude.toFixed(6);
                            updateMarkerPosition();
                        }
                    },
                    function(error) {
                        console.log('Geolocation error:', error);
                    }, {
                        timeout: 5000
                    }
                );
            }
        });
    </script>
@endsection
