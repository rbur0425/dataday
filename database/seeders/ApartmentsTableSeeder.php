<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = storage_path('app/scrape/combined_listings.csv');

        // Check if file exists
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        // Open CSV file
        $file = fopen($csvFile, 'r');

        // Skip header row
        $header = fgetcsv($file);

        // Process each row
        while (($row = fgetcsv($file)) !== false) {
            try {
                // Check if latitude and longitude are available in the CSV row
                $latitude = !empty($row[8]) ? (float) $row[8] : null;
                $longitude = !empty($row[9]) ? (float) $row[9] : null;
                
                DB::table('apartments')->insert([
                    'complex_name' => $row[0],
                    'street_address' => $row[1],
                    'min_price' => (float) str_replace(['$', ','], '', $row[2]),
                    'max_price' => (float) str_replace(['$', ','], '', $row[3]),
                    'types_available' => $row[4],
                    'square_footage' => !empty($row[5]) ? (int) $row[5] : null,
                    'primary_image_url' => $row[6],
                    'phone_number' => !empty($row[7]) ? $row[7] : null,
                    'latitude' => $latitude,  // Use latitude from CSV file
                    'longitude' => $longitude, // Use longitude from CSV file
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                $this->command->error("Error processing row: " . implode(',', $row));
                $this->command->error($e->getMessage());
            }
        }

        fclose($file);
        $this->command->info('Apartments data imported successfully!');
    }
}
