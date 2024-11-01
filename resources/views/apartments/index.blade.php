<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🕵️ Rent Detective</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}"></script>
</head>

<body class="bg-gray-100">
    <div x-data="apartmentsData()" class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">🕵️ Rent Detective</h1>

        <div class="flex gap-6">
            <!-- Listings Section (75%) -->
            <div class="w-3/4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <template x-for="apartment in apartments" :key="apartment.id">
                        <div
                            class="bg-white rounded-lg shadow-lg overflow-hidden transform transition duration-200 hover:scale-[1.02]"
                            @mouseenter="highlightMarker(apartment.id)"
                            @mouseleave="unhighlightMarker(apartment.id)">
                            <!-- Image -->
                            <div class="relative h-48">
                                <img
                                    :src="apartment.primary_image_url"
                                    :alt="apartment.complex_name"
                                    class="w-full h-full object-cover"
                                    loading="lazy">
                            </div>

                            <!-- Content -->
                            <div class="p-6">
                                <h3 class="text-xl font-bold mb-2" x-text="apartment.complex_name"></h3>
                                <p class="text-gray-600 mb-4" x-text="apartment.street_address"></p>

                                <div class="flex justify-between items-center mb-4">
                                    <div class="text-lg font-semibold text-green-600" x-text="apartment.price_range"></div>
                                    <div x-show="apartment.square_footage" class="text-sm text-gray-500" x-text="apartment.square_footage"></div>
                                </div>

                                <div class="text-sm text-gray-600 mb-4" x-text="apartment.types_available"></div>

                                <div class="flex items-center justify-between mt-4">
                                    <div x-show="apartment.phone_number" class="text-blue-600">
                                        <a :href="'tel:' + apartment.phone_number" x-text="apartment.phone_number"></a>
                                    </div>
                                    <a :href="'/apartments/' + apartment.id"
                                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md transition duration-150 ease-in-out">
                                        View Details
                                        <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Map Section (25%) -->
            <div class="w-1/4">
                <div class="sticky top-8">
                    <div id="map" class="h-[calc(100vh-4rem)] rounded-lg shadow-lg"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function apartmentsData() {
            return {
                apartments: @json($apartments),
                map: null,
                markers: {},
                infoWindows: {},

                init() {
                    this.initMap();
                    this.addMarkers();
                },

                initMap() {
                    this.map = new google.maps.Map(document.getElementById('map'), {
                        center: @json($mapCenter),
                        zoom: 13,
                        styles: [{
                            featureType: "poi",
                            elementType: "labels",
                            stylers: [{
                                visibility: "off"
                            }]
                        }]
                    });
                },

                addMarkers() {
                    this.apartments.forEach(apartment => {
                        const marker = new google.maps.Marker({
                            position: {
                                lat: parseFloat(apartment.lat),
                                lng: parseFloat(apartment.lng)
                            },
                            map: this.map,
                            title: apartment.complex_name
                        });

                        const infoWindow = new google.maps.InfoWindow({
                            content: `
                                <div class="p-2">
                                    <h3 class="font-bold">${apartment.complex_name}</h3>
                                    <p class="text-sm">${apartment.price_range}</p>
                                </div>
                            `
                        });

                        marker.addListener('mouseover', () => infoWindow.open(this.map, marker));
                        marker.addListener('mouseout', () => infoWindow.close());

                        this.markers[apartment.id] = marker;
                        this.infoWindows[apartment.id] = infoWindow;
                    });
                },

                highlightMarker(apartmentId) {
                    const marker = this.markers[apartmentId];
                    if (marker) {
                        marker.setAnimation(google.maps.Animation.BOUNCE);
                        this.infoWindows[apartmentId].open(this.map, marker);
                    }
                },

                unhighlightMarker(apartmentId) {
                    const marker = this.markers[apartmentId];
                    if (marker) {
                        marker.setAnimation(null);
                        this.infoWindows[apartmentId].close();
                    }
                }
            }
        }
    </script>
</body>

</html>