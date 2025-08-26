@extends('layouts.main-layout')

@section('title', 'Delivery Zones - ' . $restaurant->name)

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <style>
        #map {
            height: 600px;
            border-radius: 0.5rem;
            overflow: hidden;
            z-index: 1;
        }

        .zone-type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .zone-type-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .zone-type-btn.active {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        .drawing-controls {
            margin-bottom: 15px;
        }

        .test-point-form {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 0.5rem;
            margin-top: 20px;
        }

        .zone-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .zone-item {
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            margin-bottom: 10px;
            background-color: white;
        }

        .test-result {
            margin-top: 15px;
            padding: 10px;
            border-radius: 0.5rem;
        }

        .test-result.within-zone {
            background-color: #d1fae5;
            border: 1px solid #10b981;
        }

        .test-result.outside-zone {
            background-color: #fee2e2;
            border: 1px solid #ef4444;
        }
    </style>
@endsection

@section('content')
    <!-- ===== Main Content Start ===== -->
    <main>
        <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
            <!-- Breadcrumb Start -->
            <nav class="flex mb-5" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                    <li class="inline-flex items-center">
                        <a href="{{ route('restaurants.index') }}"
                            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                            <i class="fas fa-home mr-2"></i>
                            Restaurants
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Delivery
                                Zones</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span
                                class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">{{ $restaurant->name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <!-- Breadcrumb End -->

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-5" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Zone Management -->
                <div class="lg:col-span-1">
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">
                            Manage Delivery Zones
                        </h3>

                        <div class="zone-type-selector">
                            <div class="zone-type-btn active" data-type="polygon">
                                <i class="fas fa-draw-polygon text-xl mb-2"></i>
                                <div>Polygon Zone</div>
                            </div>
                            <div class="zone-type-btn" data-type="radius">
                                <i class="fas fa-circle text-xl mb-2"></i>
                                <div>Radius Zone</div>
                            </div>
                        </div>

                        <form id="zoneForm" class="space-y-4">
                            @csrf
                            <div>
                                <label for="zoneName"
                                    class="block mb-2 text-sm font-medium text-gray-800 dark:text-white/90">Zone
                                    Name</label>
                                <input type="text" id="zoneName" name="name"
                                    class="w-full px-4 py-2.5 text-theme-sm leading-5 rounded-lg border border-gray-200 bg-white placeholder-gray-400 focus:border-primary focus:ring-0 dark:bg-white/5 dark:border-gray-800 dark:text-white/90 dark:focus:border-primary"
                                    placeholder="Enter zone name" required>
                            </div>

                            <div id="radiusFields" class="hidden">
                                <label for="zoneRadius"
                                    class="block mb-2 text-sm font-medium text-gray-800 dark:text-white/90">Radius
                                    (km)</label>
                                <input type="number" id="zoneRadius" name="radius" step="0.1" min="0.1"
                                    value="2"
                                    class="w-full px-4 py-2.5 text-theme-sm leading-5 rounded-lg border border-gray-200 bg-white placeholder-gray-400 focus:border-primary focus:ring-0 dark:bg-white/5 dark:border-gray-800 dark:text-white/90 dark:focus:border-primary">
                            </div>

                            <div class="drawing-controls">
                                <button type="button" id="drawZoneBtn"
                                    class="w-full bg-blue-600 text-white py-2.5 px-4 rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-draw-polygon mr-2"></i> Start Drawing
                                </button>
                                <button type="button" id="clearMapBtn"
                                    class="w-full bg-gray-200 text-gray-800 py-2.5 px-4 rounded-lg hover:bg-gray-300 transition mt-2 dark:bg-gray-700 dark:text-white/90 dark:hover:bg-gray-600">
                                    <i class="fas fa-trash mr-2"></i> Clear Drawing
                                </button>
                            </div>

                            <button type="submit"
                                class="w-full bg-green-600 text-white py-2.5 px-4 rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-save mr-2"></i> Save Delivery Zone
                            </button>
                        </form>

                        <div class="test-point-form">
                            <h4 class="font-medium text-gray-800 dark:text-white/90 mb-3">Test Delivery Point</h4>
                            <form id="testPointForm" class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="testLat"
                                            class="block mb-1 text-sm font-medium text-gray-800 dark:text-white/90">Latitude</label>
                                        <input type="number" step="any" id="testLat"
                                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200"
                                            placeholder="Latitude">
                                    </div>
                                    <div>
                                        <label for="testLng"
                                            class="block mb-1 text-sm font-medium text-gray-800 dark:text-white/90">Longitude</label>
                                        <input type="number" step="any" id="testLng"
                                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200"
                                            placeholder="Longitude">
                                    </div>
                                </div>
                                <button type="submit"
                                    class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition text-sm">
                                    Test Point
                                </button>
                            </form>
                            <div id="testResult" class="test-result hidden"></div>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 mt-6">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">
                            Existing Delivery Zones
                        </h3>
                        <div class="zone-list">
                            @if ($restaurant->deliveryZones->count() > 0)
                                @foreach ($restaurant->deliveryZones as $zone)
                                    <div class="zone-item">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <h4 class="font-medium">{{ $zone->name }}</h4>
                                                <p class="text-sm text-gray-600">
                                                    Type: {{ ucfirst($zone->type) }}
                                                    @if ($zone->type == 'radius')
                                                        | Radius: {{ $zone->radius }} km
                                                    @endif
                                                </p>
                                            </div>
                                            <form
                                                action="{{ route('restaurants.delete-delivery-zone', [$restaurant->id, $zone->id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800"
                                                    onclick="return confirm('Are you sure you want to delete this zone?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-500 text-center py-4">No delivery zones defined yet.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column - Map -->
                <div class="lg:col-span-2">
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">
                            Delivery Zone Map - {{ $restaurant->name }}
                        </h3>
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map
            const map = L.map('map').setView([{{ $restaurant->latitude }}, {{ $restaurant->longitude }}], 13);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);


            // Add restaurant marker
            const restaurantMarker = L.marker([23.843450, 90.384910])
                .addTo(map)
                .bindPopup('<strong>{{ $restaurant->name }}</strong><br>Restaurant Location')
                .openPopup();

            // Variables for drawing
            let drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);
            let drawControl;
            let currentDrawingTool;

            // Zone type selection
            const zoneTypeButtons = document.querySelectorAll('.zone-type-btn');
            let selectedZoneType = 'polygon';

            zoneTypeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    zoneTypeButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    selectedZoneType = this.dataset.type;

                    // Toggle radius field
                    document.getElementById('radiusFields').classList.toggle('hidden',
                        selectedZoneType !== 'radius');

                    // Reinitialize drawing tools
                    initDrawingTools();
                });
            });

            // Initialize drawing tools based on selected type
            function initDrawingTools() {
                // Remove existing controls
                if (drawControl) {
                    map.removeControl(drawControl);
                }

                // Clear existing drawn items
                drawnItems.clearLayers();

                // Add appropriate drawing control
                if (selectedZoneType === 'polygon') {
                    drawControl = new L.Control.Draw({
                        draw: {
                            polygon: {
                                shapeOptions: {
                                    color: '#3388ff',
                                    weight: 3,
                                    opacity: 0.5,
                                    fillOpacity: 0.2
                                },
                                allowIntersection: false,
                                drawError: {
                                    color: '#e1e100',
                                    message: '<strong>Error:</strong> Polygon edges cannot cross!'
                                }
                            },
                            circle: false,
                            rectangle: false,
                            circlemarker: false,
                            marker: false,
                            polyline: false
                        },
                        edit: {
                            featureGroup: drawnItems
                        }
                    });
                } else {
                    drawControl = new L.Control.Draw({
                        draw: {
                            polygon: false,
                            circle: {
                                shapeOptions: {
                                    color: '#3388ff',
                                    weight: 3,
                                    opacity: 0.5,
                                    fillOpacity: 0.2
                                }
                            },
                            rectangle: false,
                            circlemarker: false,
                            marker: false,
                            polyline: false
                        },
                        edit: {
                            featureGroup: drawnItems
                        }
                    });
                }

                map.addControl(drawControl);
            }

            // Handle map drawing events
            map.on(L.Draw.Event.CREATED, function(e) {
                const type = e.layerType;
                const layer = e.layer;

                // Clear previous drawings
                drawnItems.clearLayers();

                // Add to feature group
                drawnItems.addLayer(layer);

                // If it's a circle, update the radius input
                if (type === 'circle') {
                    const radius = layer.getRadius() / 1000; // Convert to km
                    document.getElementById('zoneRadius').value = radius.toFixed(1);
                }
            });

            // Draw zone button handler
            document.getElementById('drawZoneBtn').addEventListener('click', function() {
                if (selectedZoneType === 'polygon') {
                    currentDrawingTool = new L.Draw.Polygon(map, drawControl.options.draw.polygon);
                } else {
                    currentDrawingTool = new L.Draw.Circle(map, drawControl.options.draw.circle);
                }
                currentDrawingTool.enable();
            });

            // Clear map button handler
            document.getElementById('clearMapBtn').addEventListener('click', function() {
                drawnItems.clearLayers();
            });

            // Form submission handler
            document.getElementById('zoneForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('type', selectedZoneType);

                // Validate drawing
                if (drawnItems.getLayers().length === 0) {
                    alert('Please draw a zone on the map first.');
                    return;
                }

                // Get coordinates based on type
                if (selectedZoneType === 'polygon') {
                    const polygon = drawnItems.getLayers().find(layer => layer instanceof L.Polygon);
                    if (!polygon) {
                        alert('Please draw a polygon on the map.');
                        return;
                    }
                    const latlngs = polygon.getLatLngs()[0].map(latlng => ({
                        lat: latlng.lat,
                        lng: latlng.lng
                    }));
                    formData.append('coordinates', JSON.stringify(latlngs));
                } else {
                    const circle = drawnItems.getLayers().find(layer => layer instanceof L.Circle);
                    if (!circle) {
                        alert('Please draw a circle on the map.');
                        return;
                    }
                    const center = circle.getLatLng();
                    formData.append('center', JSON.stringify({
                        latitude: center.lat,
                        longitude: center.lng
                    }));
                    formData.append('radius', document.getElementById('zoneRadius').value);
                }

                // Submit form via AJAX
                try {
                    const response = await fetch(
                        '{{ route('restaurants.save-delivery-zone', $restaurant->id) }}', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });

                    const result = await response.json();

                    if (result.success) {
                        alert('Zone saved successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + result.message);
                        if (result.errors) {
                            console.error(result.errors);
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while saving the zone.');
                }
            });

            // Test point form handler
            document.getElementById('testPointForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const lat = document.getElementById('testLat').value;
                const lng = document.getElementById('testLng').value;

                if (!lat || !lng) {
                    alert('Please enter both latitude and longitude.');
                    return;
                }

                try {
                    const response = await fetch(
                        '{{ route('restaurants.test-delivery-point', $restaurant->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                latitude: lat,
                                longitude: lng
                            })
                        });

                    const result = await response.json();
                    const testResult = document.getElementById('testResult');

                    if (result.success) {
                        testResult.classList.remove('hidden');
                        if (result.within_zone) {
                            testResult.className = 'test-result within-zone';
                            testResult.innerHTML = `
                            <strong>✓ Within Delivery Zone</strong>
                            <p class="mt-1">This point is within the following zones: ${result.matching_zones.join(', ')}</p>
                        `;
                        } else {
                            testResult.className = 'test-result outside-zone';
                            testResult.innerHTML = `
                            <strong>✗ Outside Delivery Zones</strong>
                            <p class="mt-1">This point is not within any delivery zones.</p>
                        `;
                        }
                    } else {
                        alert('Error testing point: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while testing the point.');
                }
            });

            // Load existing zones on the map
            @foreach ($restaurant->deliveryZones as $zone)
                @if ($zone->type == 'polygon')
                    const polygonCoords = JSON.parse('{!! $zone->coordinates !!}');
                    const polygon = L.polygon(
                        polygonCoords.map(coord => [coord.lat, coord.lng]), {
                            color: '#3388ff',
                            weight: 3,
                            opacity: 0.7,
                            fillOpacity: 0.2
                        }
                    ).addTo(map);
                    polygon.bindPopup('<strong>{{ $zone->name }}</strong><br>Polygon Delivery Zone');
                @else
                    // const center = JSON.parse('{!! $zone->center !!}');
                    // const circle = L.circle(
                    //     [center.latitude, center.longitude], {
                    //         radius: {{ $zone->radius * 1000 }},
                    //         color: '#3388ff',
                    //         weight: 3,
                    //         opacity: 0.7,
                    //         fillOpacity: 0.2
                    //     }
                    // ).addTo(map);
                    // circle.bindPopup('<strong>{{ $zone->name }}</strong><br>Radius: {{ $zone->radius }} km');
                @endif
            @endforeach

            // Initialize drawing tools
            initDrawingTools();
        });
    </script>
@endsection
