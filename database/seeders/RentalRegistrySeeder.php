<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RentalRegistrySeeder extends Seeder
{
    protected $batchSize = 1000;

    public function run()
    {
        ini_set('memory_limit', '512M');

        $csvFile = storage_path('app/dataday/Syracuse_Rental_Registry.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found at: {$csvFile}");
            return;
        }

        $handle = fopen($csvFile, 'r');

        // Get headers and remove any BOM
        $csvHeaders = array_map(function ($header) {
            return strtolower(trim(str_replace("\xEF\xBB\xBF", '', $header)));
        }, fgetcsv($handle));

        $this->command->info("Processing CSV headers: " . implode(', ', $csvHeaders));

        // Create column mapping
        $columnIndexes = array_flip($csvHeaders);

        // Truncate the table
        DB::table('rental_registries')->truncate();

        $rowNumber = 2;
        $insertedCount = 0;
        $errorCount = 0;
        $batch = [];

        while (!feof($handle)) {
            try {
                $row = fgetcsv($handle);

                if ($row === false) {
                    continue;
                }

                // Clear memory periodically
                if ($rowNumber % 1000 === 0) {
                    gc_collect_cycles();
                }

                try {
                    $data = [
                        'x_coordinate' => $this->parseNumeric($row[$columnIndexes['x']] ?? null),
                        'y_coordinate' => $this->parseNumeric($row[$columnIndexes['y']] ?? null),
                        'sbl' => $row[$columnIndexes['sbl']] ?? null,
                        'property_address' => $row[$columnIndexes['propertyaddress']] ?? null,
                        'zip' => $row[$columnIndexes['zip']] ?? null,
                        'needs_rr' => $row[$columnIndexes['needsrr']] ?? null,
                        'inspect_period' => $this->parseDateTime($row[$columnIndexes['inspect_period']] ?? null),
                        'completion_type_name' => $row[$columnIndexes['completion_type_name']] ?? null,
                        'completion_date' => $this->parseDateTime($row[$columnIndexes['completion_date']] ?? null),
                        'valid_until' => $this->parseDateTime($row[$columnIndexes['valid_until']] ?? null),
                        'rr_is_valid' => $row[$columnIndexes['rrisvalid']] ?? null,
                        'rr_app_received' => $this->parseDateTime($row[$columnIndexes['rr_app_received']] ?? null),
                        'rr_ext_insp_pass' => $this->parseDateTime($row[$columnIndexes['rr_ext_insp_pass']] ?? null),
                        'rr_ext_insp_fail' => $this->parseDateTime($row[$columnIndexes['rr_ext_insp_fail']] ?? null),
                        'rr_int_insp_fail' => $this->parseDateTime($row[$columnIndexes['rr_int_insp_fail']] ?? null),
                        'rr_int_insp_pass' => $this->parseDateTime($row[$columnIndexes['rr_int_insp_pass']] ?? null),
                        'rr_contact_name' => $row[$columnIndexes['rr_contact_name']] ?? null,
                        'pc_owner' => $row[$columnIndexes['pc_owner']] ?? null,
                        'latitude' => $this->parseNumeric($row[$columnIndexes['latitude']] ?? null),
                        'longitude' => $this->parseNumeric($row[$columnIndexes['longitude']] ?? null),
                        'shape' => $row[$columnIndexes['shape']] ?? null,
                        'object_id' => isset($row[$columnIndexes['objectid']]) ? (int)$row[$columnIndexes['objectid']] : null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    $batch[] = $data;
                    $insertedCount++;

                    if (count($batch) >= $this->batchSize) {
                        $this->insertBatch($batch);
                        $batch = [];
                        $this->command->info("Processed {$insertedCount} records...");
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning("Error processing row {$rowNumber}: " . $e->getMessage());
                    continue;
                }

                $rowNumber++;
            } catch (\Exception $e) {
                Log::error("Fatal error processing row {$rowNumber}: " . $e->getMessage());
                throw $e;
            }
        }

        // Insert any remaining records
        if (!empty($batch)) {
            $this->insertBatch($batch);
        }

        fclose($handle);

        $finalCount = DB::table('rental_registries')->count();
        $this->command->info("Import completed successfully!");
        $this->command->info("Total records processed: " . ($rowNumber - 2));
        $this->command->info("Records inserted: " . $insertedCount);
        $this->command->info("Records with errors: " . $errorCount);
        $this->command->info("Final count in database: " . $finalCount);
    }

    private function insertBatch(array $batch)
    {
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            try {
                DB::beginTransaction();
                DB::table('rental_registries')->insert($batch);
                DB::commit();
                return;
            } catch (\Exception $e) {
                DB::rollBack();
                $attempts++;

                if ($attempts === $maxAttempts) {
                    throw $e;
                }

                sleep(1);
            }
        }
    }

    private function parseDateTime($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Remove timezone offset
            $value = preg_replace('/\+\d{2}:\d{2}$/', '', $value);
            $value = preg_replace('/\+\d{2}$/', '', $value);

            $date = Carbon::parse($value);

            if ($date->year < 1971) {
                return null;
            }

            return $date->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseNumeric($value)
    {
        if (empty($value) || $value === ' ') return null;
        return is_numeric($value) ? (float)$value : null;
    }
}
