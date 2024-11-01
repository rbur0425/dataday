<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $apartmentData['complex_name'] }} - Rent Detective</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        </div>
    </main>
</body>

</html>