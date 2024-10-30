<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyAssessmentSeeder extends Seeder
{
    public function run()
    {
        $csvFile = storage_path('app/dataday/Assessment_Final_Roll_(2024).csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found at: {$csvFile}");
            return;
        }

        $handle = fopen($csvFile, 'r');

        // Get the first line to inspect headers
        $firstLine = fgets($handle);
        $this->command->info("First line of file: " . $firstLine);

        // Reset file pointer
        rewind($handle);

        // Get headers and remove any BOM and trim
        $csvHeaders = fgetcsv($handle);
        $csvHeaders = array_map(function ($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, $csvHeaders);

        $this->command->info("Processed headers: " . implode(', ', $csvHeaders));

        // Find the index of the SBL column
        $sblIndex = array_search('SBL', $csvHeaders);
        if ($sblIndex === false) {
            throw new \Exception("SBL column not found in headers");
        }

        // Truncate the table outside of the main transaction
        DB::table('property_assessments')->truncate();

        $rowNumber = 2;
        $insertedCount = 0;
        $batch = [];
        $batchSize = 1000;

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $data = [
                    'sbl' => trim($row[$sblIndex]),
                    'property_address' => isset($row[1]) ? trim($row[1]) : null,
                    'property_city' => isset($row[2]) ? trim($row[2]) : null,
                    'dimensions' => isset($row[3]) ? trim($row[3]) : null,
                    'property_class' => isset($row[4]) ? trim($row[4]) : null,
                    'prop_class_description' => isset($row[5]) ? trim($row[5]) : null,
                    'primary_owner' => isset($row[6]) ? trim($row[6]) : null,
                    'secondary_owner' => isset($row[7]) ? trim($row[7]) : null,
                    'owner_address' => isset($row[8]) ? trim($row[8]) : null,
                    'po_box' => isset($row[9]) ? trim($row[9]) : null,
                    'school_taxable' => isset($row[10]) ? (float) str_replace(['$', ','], '', trim($row[10])) : null,
                    'municipality_taxable' => isset($row[11]) ? (float) str_replace(['$', ','], '', trim($row[11])) : null,
                    'county_taxable' => isset($row[12]) ? (float) str_replace(['$', ','], '', trim($row[12])) : null,
                    'total_assessment' => isset($row[13]) ? (float) str_replace(['$', ','], '', trim($row[13])) : null,
                    'exemption_1_description' => isset($row[14]) ? trim($row[14]) : null,
                    'exemption_1_amt' => isset($row[15]) ? (float) str_replace(['$', ','], '', trim($row[15])) : null,
                    'exemption_2_description' => isset($row[16]) ? trim($row[16]) : null,
                    'exemption_2_amt' => isset($row[17]) ? (float) str_replace(['$', ','], '', trim($row[17])) : null,
                    'exemption_3_description' => isset($row[18]) ? trim($row[18]) : null,
                    'exemption_3_amt' => isset($row[19]) ? (float) str_replace(['$', ','], '', trim($row[19])) : null,
                    'exemption_4_description' => isset($row[20]) ? trim($row[20]) : null,
                    'exemption_4_amt' => isset($row[21]) ? (float) str_replace(['$', ','], '', trim($row[21])) : null,
                    'exemption_5_description' => isset($row[22]) ? trim($row[22]) : null,
                    'exemption_5_amt' => isset($row[23]) ? (float) str_replace(['$', ','], '', trim($row[23])) : null,
                    'exemption_6_description' => isset($row[24]) ? trim($row[24]) : null,
                    'exemption_6_amt' => isset($row[25]) ? (float) str_replace(['$', ','], '', trim($row[25])) : null,
                    'object_id' => isset($row[26]) ? (int) trim($row[26]) : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                if (!empty($data['sbl'])) {
                    $batch[] = $data;
                    $insertedCount++;

                    if (count($batch) >= $batchSize) {
                        // Use a separate transaction for each batch
                        DB::beginTransaction();
                        try {
                            DB::table('property_assessments')->insert($batch);
                            DB::commit();
                            $this->command->info("Processed {$insertedCount} records...");
                            $batch = [];
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    }
                }

                $rowNumber++;
            }

            // Insert any remaining records
            if (!empty($batch)) {
                DB::beginTransaction();
                try {
                    DB::table('property_assessments')->insert($batch);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            $finalCount = DB::table('property_assessments')->count();
            $this->command->info("Import completed successfully!");
            $this->command->info("Total records processed: " . ($rowNumber - 2));
            $this->command->info("Records inserted: " . $insertedCount);
            $this->command->info("Final count in database: " . $finalCount);
        } catch (\Exception $e) {
            $this->command->error("Import failed: " . $e->getMessage());
            throw $e;
        } finally {
            fclose($handle);
        }
    }
}
