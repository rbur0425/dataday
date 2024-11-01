<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApartmentController extends Controller
{
    public function index()
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
}
