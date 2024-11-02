<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $apartmentData['complex_name'] }} - Rent Detective</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Include marked.js for markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <a href="/apartments" class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">🕵️ Rent Detective</h1>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <div class="mb-6">
            <a href="{{ route('apartments.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Back to Listings</span>
            </a>
        </div>
        <!-- Property Image -->
        <div class="relative h-96 rounded-xl overflow-hidden mb-8">
            <img
                src="{{ $apartmentData['primary_image_url'] }}"
                alt="{{ $apartmentData['complex_name'] }}"
                class="w-full h-full object-cover">
        </div>

        <!-- Property Details -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $apartmentData['complex_name'] }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-600">{{ $apartmentData['street_address'] }}</p>
                    <p class="text-2xl font-semibold text-green-600 mt-2">{{ $apartmentData['price_range'] }}</p>
                    <p class="text-gray-700 mt-2">{{ $apartmentData['types_available'] }}</p>
                    @if($apartmentData['square_footage'])
                    <p class="text-gray-600 mt-2">{{ $apartmentData['square_footage'] }}</p>
                    @endif
                    @if($apartmentData['phone_number'])
                    <p class="text-blue-600 mt-4">
                        <a href="tel:{{ $apartmentData['phone_number'] }}">{{ $apartmentData['phone_number'] }}</a>
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Rent vs Buy Component -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-900 mb-4">Rent vs Buy</h3>
            <p class="text-gray-600">With a monthly rent payment of <strong>${{ number_format($apartmentData['max_price']) }}</strong>, here's the estimated home price you could afford with a 7.343% interest rate.</p>

            @php
            $monthlyRate = 7.343 / 100 / 12;
            $loanTermMonths = 30 * 12;
            $purchasePrice = ($apartmentData['max_price'] * (1 - pow(1 + $monthlyRate, -$loanTermMonths))) / $monthlyRate;
            @endphp

            <p class="text-gray-900 font-medium text-lg mt-4">Estimated Purchase Price: <span class="text-green-600">${{ number_format($purchasePrice, 2) }}</span></p>
            <p class="text-sm text-gray-500 mt-2">Based on a 30-year mortgage at 7.343% interest.</p>
            <p class="text-xs text-gray-500 mt-2 italic">Note: This estimate does not include taxes, home insurance, or private mortgage insurance (PMI).</p>
        </div>

        <!-- Rental Forecast Component -->
        @if($rentalForecast['has_data'] && $rentalForecast['current_price'] > 0 && $rentalForecast['sample_size'] > 0)
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-900 mb-4">Rental Market Trends</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <p class="text-gray-600">Projected Annual Change</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $rentalForecast['average_growth_rate'] }}%
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-600">Estimated Annual Change</p>
                        <p class="text-2xl font-bold text-gray-900">
                            ${{ number_format(abs($rentalForecast['forecast_amount'])) }}
                            <span class="text-sm font-normal text-gray-600">
                                per year
                            </span>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Highest Monthly Growth</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $rentalForecast['max_growth_rate'] }}%
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Lowest Monthly Growth</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $rentalForecast['min_growth_rate'] }}%
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 mt-4">
                    <p class="text-sm text-gray-600">Current Market Rate</p>
                    <p class="text-lg font-semibold text-gray-900">
                        ${{ number_format($rentalForecast['current_price'], 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Data for ZIP code: {{ $rentalForecast['zip_code'] }}
                    </p>
                </div>

                <div class="mt-4">
                    <p class="text-sm text-gray-500">
                        *Analysis based on rental data from {{ $rentalForecast['date_range'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        Note: Projections are based on historical trends and may not reflect future market conditions.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Rent Negotiation Component -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-900 mb-4">Rent Negotiation</h3>
            <p class="text-gray-600 mb-4">Negotiate a lower rent based on the current code violations for this property.</p>

            <div x-data="rentNegotiation()" class="text-center">
                <button
                    x-show="!negotiationScript"
                    @click="generateScript"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Generate Script
                </button>

                <template x-if="negotiationScript">
                    <div class="negotiation-script mt-4">
                        <h3 class="text-lg font-semibold text-gray-900">Negotiation Script:</h3>
                        <!-- Render markdown as HTML using x-html -->
                        <div class="text-gray-700 mt-2" x-html="renderMarkdown(negotiationScript)"></div>
                    </div>
                </template>
            </div>
        </div>

        <script>
            // Check if marked is loaded as an object with parse method
            console.log(typeof marked); // To check if marked is loaded correctly

            function rentNegotiation() {
                return {
                    codeViolations: @json($formattedViolations),
                    negotiationScript: '',

                    generateScript() {
                        const nearbyViolations = this.codeViolations.filter(
                            violation => parseFloat(violation.distance_miles) === 0.00
                        );

                        const codeViolationsList = nearbyViolations.length > 0 ?
                            nearbyViolations.map(violation => violation.violation).join(', ') :
                            false;

                        fetch('/apartments/generate-negotiation-script', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                                body: JSON.stringify({
                                    code_violations: codeViolationsList
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log(data); // Log the entire response to confirm structure
                                this.negotiationScript = data.choices[0]?.message?.content || "No content received.";
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                    },

                    // Function to render markdown as HTML
                    renderMarkdown(content) {
                        // Remove horizontal rules (e.g., "---" or "___")
                        const cleanedContent = content.replace(/^\s*[-_]{3,}\s*$/gm, '');

                        // Parse cleaned markdown content to HTML
                        if (marked && typeof marked.parse === 'function') {
                            return marked.parse(cleanedContent);
                        } else if (typeof marked === 'function') {
                            return marked(cleanedContent);
                        } else {
                            console.error('Marked library not loaded correctly.');
                            return cleanedContent; // Return cleaned markdown if marked isn't available
                        }
                    }
                }
            }
        </script>


        <!-- Green Section Component -->
        <div class="bg-green-50 rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-2xl font-semibold text-green-900 mb-4">Environmental Quality & Green Initiatives</h3>
            <p class="text-gray-600 mb-4">Check out the environmental factors and green initiatives in the neighborhood of this property.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Air Quality -->
                <div class="flex items-center">
                    <i class="fas fa-wind text-green-600 text-2xl mr-3"></i>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">Air Quality</p>
                        <p class="text-gray-600">
                            Current AQI:
                            <span class="font-medium text-green-700">{{ $airQualityIndex['index'] ?? 'Loading...' }}</span> -
                            <span class="{{ $airQualityIndex['color'] ?? 'text-gray-500' }}">
                                {{ $airQualityIndex['label'] ?? 'Loading...' }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Walkability -->
                <div class="flex items-center">
                    <i class="fas fa-walking text-green-600 text-2xl mr-3"></i>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">Walkability</p>
                        <p class="text-gray-600">
                            Score: <span class="font-medium text-green-700">{{ $walkScore['score'] ?? 'Loading...' }}</span> -
                            <span class="{{ $walkScore['color'] ?? 'text-gray-500' }}">
                                {{ $walkScore['label'] ?? 'Loading...' }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Bikeability -->
                <div class="flex items-center">
                    <i class="fas fa-bicycle text-green-600 text-2xl mr-3"></i>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">Bikeability</p>
                        <p class="text-gray-600">
                            Score: <span class="font-medium text-green-700">{{ $bikeScore['score'] ?? 'Loading...' }}</span> -
                            <span class="{{ $bikeScore['color'] ?? 'text-gray-500' }}">
                                {{ $bikeScore['label'] ?? 'Loading...' }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Green Spaces -->
                <div class="flex items-center">
                    <i class="fas fa-tree text-green-600 text-2xl mr-3"></i>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">Green Spaces</p>
                        <p class="text-gray-600">
                            Nearby Parks: <span class="font-medium text-green-700">{{ count($nearbyParks) ?? 'N/A' }}</span>
                        </p>
                        <p class="text-xs text-gray-500 italic">*Within a 0.13 mile radius</p>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-500 mt-4 italic">*Data provided by Google Maps API, APICN Org and other environmental sources.</p>
        </div>

        <!-- Nearby Parks Component -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-2xl font-semibold text-green-900 mb-4">Nearby Parks</h3>
            <p class="text-gray-600 mb-4">Explore the parks in the vicinity of this property.</p>

            @if(count($nearbyParks) > 0)
            <ul class="space-y-4">
                @foreach(array_slice($nearbyParks, 0, 5) as $park)
                <li class="flex items-center">
                    <i class="fas fa-tree text-green-600 text-xl mr-3"></i>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">{{ $park['name'] }}
                            @if(isset($park['rating']))
                            <span class="text-yellow-500 ml-2">⭐ {{ $park['rating'] }}</span>
                            @endif
                        </p>
                        <a href="https://www.google.com/maps/place/?q=place_id:{{ $park['place_id'] }}"
                            target="_blank"
                            class="text-blue-500 hover:underline">
                            View on Google Maps
                        </a>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-gray-500">No parks found within a 2 km radius.</p>
            @endif
        </div>


        <!-- Nearby Information Tabs -->
        <div x-data="{ activeTab: 'violations' }" class="bg-white rounded-xl shadow-sm p-6">
            <div class="border-b border-gray-200 mb-6">
                <nav class="flex -mb-px space-x-8">
                    <button
                        @click="activeTab = 'violations'"
                        :class="{'border-blue-500 text-blue-600': activeTab === 'violations'}"
                        class="border-b-2 py-4 px-1 text-sm font-medium">
                        Code Violations ({{ count($violations) }})
                    </button>
                    <button
                        @click="activeTab = 'assessments'"
                        :class="{'border-blue-500 text-blue-600': activeTab === 'assessments'}"
                        class="border-b-2 py-4 px-1 text-sm font-medium">
                        Property Assessments ({{ count($assessments) }})
                    </button>
                    <button
                        @click="activeTab = 'vacant'"
                        :class="{'border-blue-500 text-blue-600': activeTab === 'vacant'}"
                        class="border-b-2 py-4 px-1 text-sm font-medium">
                        Vacant Properties ({{ count($vacantProperties) }})
                    </button>
                    <!-- New Rental Registries Tab -->
                    <button
                        @click="activeTab = 'rental_registries'"
                        :class="{'border-blue-500 text-blue-600': activeTab === 'rental_registries'}"
                        class="border-b-2 py-4 px-1 text-sm font-medium">
                        Other Nearby Rentals ({{ count($rentalRegistries) }})
                    </button>
                </nav>
            </div>

            <!-- Violations Tab -->
            <div x-show="activeTab === 'violations'" class="space-y-4">
                @if(count($violations) > 0)
                @foreach($violations as $violation)
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between">
                        <h4 class="font-medium">{{ $violation->complaint_address }}</h4>
                        <span class="text-sm text-gray-500">{{ number_format($violation->distance_miles, 2) }} miles away</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">{{ $violation->violation }}</p>
                    <div class="mt-2 flex justify-between">
                        <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</span>
                        <span class="text-sm px-2 py-1 rounded-full {{ $violation->status_type_name === 'Closed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $violation->status_type_name }}
                        </span>
                    </div>
                </div>
                @endforeach
                @else
                <p class="text-gray-500 text-center py-4">No code violations found within 0.5 miles</p>
                @endif
            </div>

            <!-- Assessments Tab -->
            <div x-show="activeTab === 'assessments'" class="space-y-4">
                @if(count($assessments) > 0)
                @foreach($assessments as $assessment)
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between">
                        <h4 class="font-medium">{{ $assessment->property_address }}</h4>
                        <span class="text-sm text-gray-500">{{ number_format($assessment->distance_miles, 2) }} miles away</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">{{ $assessment->prop_class_description }}</p>
                    <p class="text-sm font-medium text-gray-900 mt-2">
                        Assessment: ${{ number_format($assessment->total_assessment) }}
                    </p>
                    <a href="https://www.zillow.com/homes/{{ urlencode($assessment->property_address . ' Syracuse, NY') }}"
                        class="text-blue-500 underline text-sm mt-2 block"
                        target="_blank" rel="noopener noreferrer">
                        View on Zillow
                    </a>
                </div>
                @endforeach
                @else
                <p class="text-gray-500 text-center py-4">No property assessments found within 0.5 miles</p>
                @endif
            </div>


            <!-- Vacant Properties Tab -->
            <div x-show="activeTab === 'vacant'" class="space-y-4">
                @if(count($vacantProperties) > 0)
                @foreach($vacantProperties as $property)
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between">
                        <h4 class="font-medium">{{ $property->property_address }}</h4>
                        <span class="text-sm text-gray-500">{{ number_format($property->distance_miles, 2) }} miles away</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Type: {{ $property->vacant_type }}</p>
                    @if($property->completion_date)
                    <p class="text-sm text-gray-500 mt-2">
                        Completion: {{ \Carbon\Carbon::parse($property->completion_date)->format('M d, Y') }}
                    </p>
                    @endif
                    <a href="https://www.zillow.com/homes/{{ urlencode($property->property_address . ' Syracuse, NY') }}"
                        class="text-blue-500 underline text-sm mt-2 block"
                        target="_blank" rel="noopener noreferrer">
                        View on Zillow
                    </a>
                </div>
                @endforeach
                @else
                <p class="text-gray-500 text-center py-4">No vacant properties found within 0.5 miles</p>
                @endif
            </div>

            <!-- Rental Registries Tab Content -->
            <div x-show="activeTab === 'rental_registries'" class="space-y-4">
                @if(count($rentalRegistries) > 0)
                @foreach($rentalRegistries as $registry)
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between">
                        <h4 class="font-medium">{{ $registry->property_address }}</h4>
                        <span class="text-sm text-gray-500">{{ number_format($registry->distance_miles, 2) }} miles away</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Address: {{ $registry->property_address }}</p>
                    <p class="text-sm text-gray-600 mt-2">Inspection Period: {{ $registry->inspect_period }}</p>
                    <p class="text-sm text-gray-600 mt-2">Completion Type: {{ $registry->completion_type_name }}</p>
                    @if($registry->completion_date)
                    <p class="text-sm text-gray-500 mt-2">
                        Completion Date: {{ \Carbon\Carbon::parse($registry->completion_date)->format('M d, Y') }}
                    </p>
                    @endif
                    <p class="text-sm text-gray-600 mt-2">Needs RR: {{ $registry->needs_rr ? 'Yes' : 'No' }}</p>
                    @if($registry->valid_until)
                    <p class="text-sm mt-2 {{ \Carbon\Carbon::parse($registry->valid_until)->isPast() ? 'text-red-500' : 'text-gray-500' }}">
                        Valid Until: {{ \Carbon\Carbon::parse($registry->valid_until)->format('M d, Y') }}
                    </p>
                    @endif
                    <p class="text-sm text-gray-600 mt-2">RR Valid: {{ $registry->rr_is_valid ? 'Yes' : 'No' }}</p>
                    <p class="text-sm font-medium text-gray-900 mt-2">
                        Date application received: {{ \Carbon\Carbon::parse($registry->rr_app_received)->format('M d, Y') }}
                    </p>
                </div>
                @endforeach
                @else
                <p class="text-gray-500 text-center py-4">No rental registries found within {{ self::SEARCH_RADIUS_MILES }} miles</p>
                @endif
            </div>

        </div>
    </main>
</body>

</html>