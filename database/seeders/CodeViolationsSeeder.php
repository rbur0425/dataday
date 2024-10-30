<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CodeViolationsSeeder extends Seeder
{
    protected $batchSize = 500; // Reduced batch size

    public function run()
    {
        // Increase memory limit
        ini_set('memory_limit', '512M');

        $csvFile = storage_path('app/dataday/Code_Violations.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found at: {$csvFile}");
            return;
        }

        $handle = fopen($csvFile, 'r');

        // Get headers and remove any BOM
        $csvHeaders = array_map(function ($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, fgetcsv($handle));

        $this->command->info("Processing CSV headers: " . implode(', ', $csvHeaders));

        // Create column mapping
        $columnIndexes = array_flip($csvHeaders);

        // Truncate the table
        DB::table('code_violations')->truncate();

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
                        'x_coordinate' => $this->parseNumeric($row[$columnIndexes['X']] ?? null),
                        'y_coordinate' => $this->parseNumeric($row[$columnIndexes['Y']] ?? null),
                        'violation_number' => $row[$columnIndexes['violation_number']] ?? null,
                        'complaint_address' => $row[$columnIndexes['complaint_address']] ?? null,
                        'complaint_zip' => $row[$columnIndexes['complaint_zip']] ?? null,
                        'sbl' => $row[$columnIndexes['SBL']] ?? null,
                        'violation' => $row[$columnIndexes['violation']] ?? null,
                        'violation_date' => $this->parseDateTime($row[$columnIndexes['violation_date']] ?? null),
                        'comply_by_date' => $this->parseDateTime($row[$columnIndexes['comply_by_date']] ?? null),
                        'status_type_name' => $row[$columnIndexes['status_type_name']] ?? null,
                        'complaint_number' => $row[$columnIndexes['complaint_number']] ?? null,
                        'complaint_type_name' => $row[$columnIndexes['complaint_type_name']] ?? null,
                        'open_date' => $this->parseDateTime($row[$columnIndexes['open_date']] ?? null),
                        'owner_name' => $row[$columnIndexes['owner_name']] ?? null,
                        'inspector_id' => $row[$columnIndexes['inspector_id']] ?? null,
                        'neighborhood' => $row[$columnIndexes['Neighborhood']] ?? null,
                        'vacant' => $this->parseBoolean($row[$columnIndexes['Vacant']] ?? null),
                        'owner_address' => $row[$columnIndexes['owner_address']] ?? null,
                        'owner_city' => $row[$columnIndexes['owner_city']] ?? null,
                        'owner_state' => $row[$columnIndexes['owner_state']] ?? null,
                        'owner_zip_code' => $row[$columnIndexes['owner_zip_code']] ?? null,
                        'latitude' => $this->parseNumeric($row[$columnIndexes['Latitude']] ?? null),
                        'longitude' => $this->parseNumeric($row[$columnIndexes['Longitude']] ?? null),
                        'object_id' => isset($row[$columnIndexes['ObjectId']]) ? (int)$row[$columnIndexes['ObjectId']] : null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    if (!empty($data['violation_number'])) {
                        $batch[] = $data;
                        $insertedCount++;

                        if (count($batch) >= $this->batchSize) {
                            $this->insertBatch($batch);
                            $batch = [];

                            if ($insertedCount % 1000 === 0) {
                                $this->command->info("Processed {$insertedCount} records...");
                            }
                        }
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

        $finalCount = DB::table('code_violations')->count();
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
                DB::table('code_violations')->insert($batch);
                DB::commit();
                return;
            } catch (\Exception $e) {
                DB::rollBack();
                $attempts++;

                if ($attempts === $maxAttempts) {
                    throw $e;
                }

                // Wait before retrying
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
            // Remove the timezone part
            $value = preg_replace('/\.\d+\+\d{2}:\d{2}$/', '', $value);
            $value = preg_replace('/\+\d{2}:\d{2}$/', '', $value);

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
        if (empty($value)) return null;
        return is_numeric($value) ? (float)$value : null;
    }

    private function parseBoolean($value)
    {
        if (empty($value)) return null;
        return in_array(strtolower($value), ['1', 'true', 't', 'yes', 'y']);
    }
}
