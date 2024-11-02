<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;


class ApartmentController extends Controller
{
    const MILES_TO_METERS = 1609.34;
    const SEARCH_RADIUS_MILES = 0.5;

    public function index(): View
    {
        $apartments = DB::table('apartments')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($apartment) {
                return [
                    'id' => $apartment->id,
                    'complex_name' => $apartment->complex_name,
                    'street_address' => $apartment->street_address,
                    'min_price' => $this->formatPrice($apartment->min_price),
                    'max_price' => $this->formatPrice($apartment->max_price),
                    'price_range' => $this->getPriceRange($apartment->min_price, $apartment->max_price),
                    'types_available' => $apartment->types_available,
                    'square_footage' => $apartment->square_footage ? number_format($apartment->square_footage) . ' sq ft' : null,
                    'primary_image_url' => $apartment->primary_image_url,
                    'phone_number' => $apartment->phone_number,
                    'lat' => $apartment->latitude,
                    'lng' => $apartment->longitude,
                ];
            });

        return view('apartments.index', [
            'apartments' => $apartments,
            'mapCenter' => $this->getMapCenter($apartments),
        ]);
    }

    public function show($id)
    {
        $apartment = DB::table('apartments')
            ->where('id', $id)
            ->first();

        if (!$apartment) {
            abort(404);
        }

        $apartmentData = [
            'id' => $apartment->id,
            'complex_name' => $apartment->complex_name,
            'street_address' => $apartment->street_address,
            'price_range' => $this->getPriceRange($apartment->min_price, $apartment->max_price),
            'max_price' => $apartment->max_price,
            'types_available' => $apartment->types_available,
            'square_footage' => $apartment->square_footage ? number_format($apartment->square_footage) . ' sq ft' : null,
            'primary_image_url' => $apartment->primary_image_url,
            'phone_number' => $apartment->phone_number,
            'latitude' => $apartment->latitude,
            'longitude' => $apartment->longitude,
        ];

        $radiusMeters = self::SEARCH_RADIUS_MILES * self::MILES_TO_METERS;

        $violations = $this->getNearbyViolations($apartment->latitude, $apartment->longitude, $radiusMeters);
        $assessments = $this->getNearbyAssessments($apartment->latitude, $apartment->longitude, $radiusMeters);
        $vacantProperties = $this->getNearbyVacantProperties($apartment->latitude, $apartment->longitude, $radiusMeters);
        $rentalRegistries = $this->getNearbyRentalRegistries($apartment->latitude, $apartment->longitude, $radiusMeters);

        // Convert violations to an array and JSON-encode for JavaScript
        $formattedViolations = $violations->map(function ($violation) {
            return [
                'violation_number' => $violation->violation_number,
                'complaint_address' => $violation->complaint_address,
                'violation' => $violation->violation,
                'violation_date' => $violation->violation_date,
                'status_type_name' => $violation->status_type_name,
                'distance_miles' => $violation->distance_miles
            ];
        });

        // Fetch environmental data
        $airQualityIndex = $this->getAirQuality($apartment->latitude, $apartment->longitude);
        $walkScore = $this->getWalkabilityScore($apartment->latitude, $apartment->longitude);
        $bikeScore = $this->getBikeabilityScore($apartment->latitude, $apartment->longitude);
        $nearbyParks = $this->getNearbyParksCount($apartment->latitude, $apartment->longitude);


        return view('apartments.show', compact(
            'apartmentData',
            'violations',
            'assessments',
            'vacantProperties',
            'rentalRegistries',
            'formattedViolations',
            'airQualityIndex',
            'walkScore',
            'bikeScore',
            'nearbyParks'
        ));
    }

    private function getNearbyRentalRegistries($latitude, $longitude, $radius)
    {
        return DB::table('rental_registries')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [$longitude, $latitude, $radius])
            ->select([
                '*',
                DB::raw("ST_Distance_Sphere(point(longitude, latitude), point($longitude, $latitude)) / 1609.34 as distance_miles")
            ])
            ->orderByRaw("ST_Distance_Sphere(point(longitude, latitude), point($longitude, $latitude))")
            ->limit(20)
            ->get();
    }


    private function getNearbyViolations($lat, $lng, $radius)
    {
        $apartmentAddress = DB::table('apartments')
            ->where('latitude', $lat)
            ->where('longitude', $lng)
            ->value('street_address');

        return DB::table('code_violations')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select([
                'violation_number',
                'complaint_address',
                'violation',
                'violation_date',
                'status_type_name',
                'complaint_type_name',
                'complaint_zip',
                DB::raw("
                CASE 
                    WHEN LOWER(?) LIKE CONCAT('%', LOWER(complaint_address), '%')
                    THEN 0.00 
                    ELSE (ST_Distance_Sphere(
                        point(longitude, latitude), 
                        point(?, ?)
                    ) / 1609.34)
                END as distance_miles
            ")
            ])
            ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [$lng, $lat, $radius])
            ->orderByRaw("
            CASE 
                WHEN LOWER(?) LIKE CONCAT('%', LOWER(complaint_address), '%')
                THEN 0 
                ELSE 1 
            END")
            ->orderBy('violation_date', 'desc')
            ->orderBy('distance_miles', 'asc')
            ->setBindings([
                // Select bindings
                $apartmentAddress,
                $lng,
                $lat,
                // Where bindings
                $lng,
                $lat,
                $radius,
                // Order bindings
                $apartmentAddress
            ])
            ->limit(20)
            ->get();
    }


    private function getNearbyAssessments($lat, $lng, $radius)
    {
        return DB::table('property_assessments')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [$lng, $lat, $radius])
            ->select([
                'property_address',
                'property_class',
                'prop_class_description',
                'total_assessment',
                DB::raw("ST_Distance_Sphere(point(longitude, latitude), point($lng, $lat)) / 1609.34 as distance_miles")
            ])
            ->orderByRaw("ST_Distance_Sphere(point(longitude, latitude), point($lng, $lat))")
            ->limit(20)
            ->get();
    }

    private function getNearbyVacantProperties($lat, $lng, $radius)
    {
        return DB::table('vacant_properties')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [$lng, $lat, $radius])
            ->select([
                'property_address',
                'vacant_type',
                'vpr_result',
                'completion_date',
                DB::raw("ST_Distance_Sphere(point(longitude, latitude), point($lng, $lat)) / 1609.34 as distance_miles")
            ])
            ->orderByRaw("ST_Distance_Sphere(point(longitude, latitude), point($lng, $lat))")
            ->limit(20)
            ->get();
    }

    private function formatPrice($price)
    {
        return '$' . number_format($price);
    }

    private function getPriceRange($min, $max)
    {
        if ($min == $max) {
            return $this->formatPrice($min);
        }
        return $this->formatPrice($min) . ' - ' . $this->formatPrice($max);
    }

    private function getMapCenter($apartments)
    {
        // Center on Syracuse by default
        if ($apartments->isEmpty()) {
            return [
                'lat' => 43.0481,
                'lng' => -76.1474
            ];
        }

        // Calculate center from all apartment coordinates
        $lat = $apartments->avg('lat');
        $lng = $apartments->avg('lng');

        return compact('lat', 'lng');
    }

    public function generateNegotiationScript(Request $request)
    {
        $codeViolations = $request->input('code_violations');

        // Prepare the user message based on whether there are code violations
        if ($codeViolations) {
            $userMessage = "You are a renter looking to rent an apartment from a landlord. The landlord has the following code violations. Please make a script to negotiate a lower rent based on the code violations. Here are the code violations: " . $codeViolations;
        } else {
            $userMessage = "You are a renter looking to rent an apartment from a landlord. Make a script the renter can use to negotiate a lower rent.";
        }

        // Send the request to the GPT-4 API with structured messages
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.api_key'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an assistant who helps renters negotiate rent based on apartment conditions.'],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'max_tokens' => 2000,
        ]);

        return response()->json($response->json());
    }

    private function getAirQuality($latitude, $longitude)
    {
        $token = config('services.airqaulity.api_key');
        $url = "https://api.waqi.info/feed/geo:$latitude;$longitude/?token=$token";

        $response = Http::get($url);

        if ($response->successful() && isset($response->json()['data']['aqi'])) {
            $aqi = $response->json()['data']['aqi'];
            $category = $this->getAirQualityCategory($aqi);

            return [
                'index' => $aqi,
                'label' => $category['label'],
                'color' => $category['color']
            ];
        }

        return ['index' => 'N/A', 'label' => 'Unavailable', 'color' => 'text-gray-500'];
    }

    private function getAirQualityCategory($aqi)
    {
        if ($aqi <= 50) {
            return ['label' => 'Good', 'color' => 'text-green-700'];
        } elseif ($aqi <= 100) {
            return ['label' => 'Moderate', 'color' => 'text-yellow-600'];
        } elseif ($aqi <= 150) {
            return ['label' => 'Unhealthy for Sensitive Groups', 'color' => 'text-orange-600'];
        } elseif ($aqi <= 200) {
            return ['label' => 'Unhealthy', 'color' => 'text-red-600'];
        } elseif ($aqi <= 300) {
            return ['label' => 'Very Unhealthy', 'color' => 'text-purple-700'];
        } else {
            return ['label' => 'Hazardous', 'color' => 'text-maroon-700'];
        }
    }

    private function getWalkabilityScore($latitude, $longitude)
    {
        $apiKey = config('services.walkscore.api_key');
        $url = "https://api.walkscore.com/score";

        $response = Http::get($url, [
            'format' => 'json',
            'lat' => $latitude,
            'lon' => $longitude,
            'wsapikey' => $apiKey,
        ]);

        if ($response->successful() && isset($response->json()['walkscore'])) {
            $score = $response->json()['walkscore'];
            $category = $this->getWalkabilityCategory($score);

            return [
                'score' => $score,
                'label' => $response->json()['description'],
                'color' => $category['color']
            ];
        }

        return ['score' => 'N/A', 'label' => 'Unavailable', 'color' => 'text-gray-500'];
    }

    private function getWalkabilityCategory($score)
    {
        if ($score >= 90) {
            return ['label' => 'Walker’s Paradise', 'color' => 'text-green-700'];
        } elseif ($score >= 70) {
            return ['label' => 'Very Walkable', 'color' => 'text-green-600'];
        } elseif ($score >= 50) {
            return ['label' => 'Somewhat Walkable', 'color' => 'text-yellow-600'];
        } elseif ($score >= 25) {
            return ['label' => 'Car-Dependent', 'color' => 'text-orange-600'];
        } else {
            return ['label' => 'Car-Dependent', 'color' => 'text-red-600'];
        }
    }

    private function getBikeabilityScore($latitude, $longitude)
    {
        $apiKey = config('services.walkscore.api_key');
        $url = "https://api.walkscore.com/score";

        $response = Http::get($url, [
            'format' => 'json',
            'lat' => $latitude,
            'lon' => $longitude,
            'wsapikey' => $apiKey,
            'transit' => 0,
            'bike' => 1, // Request bike score
        ]);

        if ($response->successful() && isset($response->json()['bike']['score'])) {
            $score = $response->json()['bike']['score'];
            $category = $this->getBikeabilityCategory($score);

            return [
                'score' => $score,
                'label' => $category['label'],
                'color' => $category['color']
            ];
        }

        return ['score' => 'N/A', 'label' => 'Unavailable', 'color' => 'text-gray-500'];
    }


    private function getBikeabilityCategory($score)
    {
        if ($score >= 90) {
            return ['label' => 'Biker’s Paradise', 'color' => 'text-green-700'];
        } elseif ($score >= 70) {
            return ['label' => 'Very Bikeable', 'color' => 'text-green-600'];
        } elseif ($score >= 50) {
            return ['label' => 'Bikeable', 'color' => 'text-yellow-600'];
        } elseif ($score >= 25) {
            return ['label' => 'Somewhat Bikeable', 'color' => 'text-orange-600'];
        } else {
            return ['label' => 'Not Bikeable', 'color' => 'text-red-600'];
        }
    }

    private function getNearbyParksCount($latitude, $longitude)
    {
        $apiKey = config('services.google.maps_api_key');
        $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json";

        $response = Http::get($url, [
            'location' => "$latitude,$longitude",
            'radius' => 2000, // Radius in meters
            'type' => 'park',
            'key' => $apiKey,
        ]);

        if ($response->successful() && isset($response->json()['results'])) {
            // Return the first 5 parks with name, latitude, and longitude
            return $response->json()['results'];
        }

        return []; // Return an empty array if no parks are found
    }
}
