<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PermitRequestsSeeder extends Seeder
{
    protected $batchSize = 100;

    public function run(): void
    {
        ini_set('memory_limit', '512M');

        $csvFile = storage_path('app/dataday/permit_requests.csv');

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
        DB::table('permit_requests')->truncate();

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
                        'permit_number' => $row[$columnIndexes['Permit_Number']] ?? null,
                        'full_address' => $row[$columnIndexes['Full_Address']] ?? null,
                        'owner' => $row[$columnIndexes['Owner']] ?? null,
                        'issue_date' => $this->parseDateTime($row[$columnIndexes['Issue_Date']] ?? null),
                        'permit_type' => $row[$columnIndexes['Permit_Type']] ?? null,
                        'description_of_work' => $row[$columnIndexes['Description_of_Work']] ?? null,
                        'object_id' => isset($row[$columnIndexes['ObjectId']]) ? (int)$row[$columnIndexes['ObjectId']] : null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    if (!empty($data['permit_number'])) {
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

        $finalCount = DB::table('permit_requests')->count();
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
                DB::table('permit_requests')->insert($batch);
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
            // Remove any timezone information if present
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
}
