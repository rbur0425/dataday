<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;

class GeocodeAddresses extends Command
{
    protected $signature = 'geocode:addresses';
    protected $description = 'Geocode property addresses and update database with coordinates';

    const BATCH_SIZE = 45;
    const DELAY_BETWEEN_BATCHES = 2;

    public function handle()
    {
        $this->info('Starting geocoding process...');

        // Get all records that need geocoding (null coordinates)
        $properties = DB::table('property_assessments')
            ->whereNull('latitude')
            ->orWhereNull('longitude')
            ->get();

        $total = $properties->count();
        $processed = 0;
        $errors = 0;

        $this->info("Found {$total} addresses to process");
        $bar = $this->output->createProgressBar($total);

        // Process in batches to respect rate limits
        foreach ($properties->chunk(self::BATCH_SIZE) as $chunk) {
            foreach ($chunk as $property) {
                try {
                    $fullAddress = $this->formatAddress($property);
                    logger($fullAddress);
                    $coordinates = $this->geocodeAddress($fullAddress);

                    if ($coordinates) {
                        DB::table('property_assessments')
                            ->where('id', $property->id)
                            ->update([
                                'latitude' => $coordinates['lat'],
                                'longitude' => $coordinates['lng']
                            ]);
                        $processed++;
                    } else {
                        $this->warn("\nFailed to geocode: {$fullAddress}");
                        $errors++;
                    }
                } catch (Exception $e) {
                    $this->error("\nError processing {$property->property_address}: {$e->getMessage()}");
                    $errors++;
                }

                $bar->advance();
            }

            sleep(self::DELAY_BETWEEN_BATCHES);
        }

        $bar->finish();
        $this->info("\nGeocoding completed!");
        $this->info("Processed: {$processed}");
        $this->info("Errors: {$errors}");
    }

    private function formatAddress($property)
    {
        return implode(' ', [
            $property->property_address,
            $property->property_city,
        ]);
    }

    private function geocodeAddress($address)
    {
        $apiKey = config('services.google.maps_api_key');
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => $apiKey
        ]);

        if ($response->successful() && $response['status'] === 'OK') {
            $location = $response['results'][0]['geometry']['location'];
            return [
                'lat' => $location['lat'],
                'lng' => $location['lng']
            ];
        }

        return null;
    }
}
