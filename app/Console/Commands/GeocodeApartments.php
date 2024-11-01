<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;

class GeocodeApartments extends Command
{
    protected $signature = 'geocode:apartments';
    protected $description = 'Geocode addresses for apartments and update latitude/longitude';

    const BATCH_SIZE = 75;
    const DELAY_BETWEEN_BATCHES = 2; // seconds

    public function handle()
    {
        $this->info('Starting apartment geocoding process...');

        // Get all records that need geocoding (null coordinates)
        $apartments = DB::table('apartments')
            ->whereNull('latitude')
            ->orWhereNull('longitude')
            ->get();

        $total = $apartments->count();
        $processed = 0;
        $errors = 0;

        $this->info("Found {$total} addresses to process");
        $bar = $this->output->createProgressBar($total);

        // Process in batches to respect rate limits
        foreach ($apartments->chunk(self::BATCH_SIZE) as $chunk) {
            foreach ($chunk as $apartment) {
                try {
                    $fullAddress = $this->formatAddress($apartment);
                    $coordinates = $this->geocodeAddress($fullAddress);

                    if ($coordinates) {
                        DB::table('apartments')
                            ->where('id', $apartment->id)
                            ->update([
                                'latitude' => $coordinates['lat'],
                                'longitude' => $coordinates['lng']
                            ]);
                        $processed++;
                    } else {
                        $this->warn("\nFailed to geocode: {$fullAddress}");
                        $this->logError($apartment->id, $fullAddress, 'No coordinates returned');
                        $errors++;
                    }
                } catch (Exception $e) {
                    $this->error("\nError processing {$apartment->street_address}: {$e->getMessage()}");
                    $this->logError($apartment->id, $fullAddress, $e->getMessage());
                    $errors++;
                }

                $bar->advance();
            }

            // Delay between batches to respect rate limits
            sleep(self::DELAY_BETWEEN_BATCHES);
        }

        $bar->finish();
        $this->info("\nGeocoding completed!");
        $this->info("Processed: {$processed}");
        $this->info("Errors: {$errors}");

        if ($errors > 0) {
            $this->info("\nCheck storage/logs/geocoding_errors.log for details");
        }
    }

    private function formatAddress($apartment)
    {
        // The street_address field already includes city and state
        return $apartment->street_address;
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

        // Log the API response if there's an error
        if ($response['status'] !== 'OK') {
            $this->warn("\nGoogle API Error: " . $response['status']);
        }

        return null;
    }

    private function logError($id, $address, $error)
    {
        $logMessage = date('Y-m-d H:i:s') . " | ID: $id | Address: $address | Error: $error\n";
        $logFile = storage_path('logs/geocoding_errors.log');
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
