<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParcelMapSeeder extends Seeder
{
    protected $batchSize = 1000;

    public function run()
    {
        ini_set('memory_limit', '512M');

        $csvFile = storage_path('app/dataday/QPD_2024_08_20_L1_ODP_-8356378389195969665.csv');

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
        DB::table('parcel_maps')->truncate();

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
                    $data = $this->processRow($row, $columnIndexes);
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

        $finalCount = DB::table('parcel_maps')->count();
        $this->command->info("Import completed successfully!");
        $this->command->info("Total records processed: " . ($rowNumber - 2));
        $this->command->info("Records inserted: " . $insertedCount);
        $this->command->info("Records with errors: " . $errorCount);
        $this->command->info("Final count in database: " . $finalCount);
    }

    private function processRow($row, $columnIndexes)
    {
        $data = [
            'fid' => $this->parseInteger($row[$columnIndexes['fid']] ?? null),
            'objectid' => $this->parseInteger($row[$columnIndexes['objectid']] ?? null),
            'tax_id' => $row[$columnIndexes['tax_id']] ?? null,
            'printkey' => $row[$columnIndexes['printkey']] ?? null,
            'addressnum' => $row[$columnIndexes['addressnum']] ?? null,
            'addressnam' => $row[$columnIndexes['addressnam']] ?? null,
            'lat' => $this->parseNumeric($row[$columnIndexes['lat']] ?? null),
            'long' => $this->parseNumeric($row[$columnIndexes['long']] ?? null),
            'tax_id_1' => $row[$columnIndexes['tax_id_1']] ?? null,
            'sbl' => $row[$columnIndexes['sbl']] ?? null,
            'pnumbr' => $row[$columnIndexes['pnumbr']] ?? null,
            'st_num' => $row[$columnIndexes['stnum']] ?? null,
            'st_name' => $row[$columnIndexes['stname']] ?? null,
            'full_address' => $row[$columnIndexes['fulladdres']] ?? null,
            'zip' => $row[$columnIndexes['zip']] ?? null,
            'desc_1' => $row[$columnIndexes['desc_1']] ?? null,
            'desc_2' => $row[$columnIndexes['desc_2']] ?? null,
            'desc_3' => $row[$columnIndexes['desc_3']] ?? null,
            'shape_ind' => $this->parseInteger($row[$columnIndexes['shape_ind']] ?? null),
            'luc_parcel' => $row[$columnIndexes['luc_parcel']] ?? null,
            'lu_parcel' => $row[$columnIndexes['lu_parcel']] ?? null,
            'lucat_old' => $row[$columnIndexes['lucat_old']] ?? null,
            'land_av' => $this->parseNumeric($row[$columnIndexes['land_av']] ?? null),
            'total_av' => $this->parseNumeric($row[$columnIndexes['total_av']] ?? null),
            'owner' => $row[$columnIndexes['owner']] ?? null,
            'add1_ownpo' => $row[$columnIndexes['add1_ownpo']] ?? null,
            'add2_ownst' => $row[$columnIndexes['add2_ownst']] ?? null,
            'add3_ownun' => $row[$columnIndexes['add3_ownun']] ?? null,
            'add4_ownci' => $row[$columnIndexes['add4_ownci']] ?? null,
            'front' => $this->parseNumeric($row[$columnIndexes['front']] ?? null),
            'depth' => $this->parseNumeric($row[$columnIndexes['depth']] ?? null),
            'acres' => $this->parseNumeric($row[$columnIndexes['acres']] ?? null),
            'yr_built' => $this->parseInteger($row[$columnIndexes['yr_built']] ?? null),
            'n_resunits' => $this->parseInteger($row[$columnIndexes['n_resunits']] ?? null),
            'ipsvacant' => $row[$columnIndexes['ipsvacant']] ?? null,
            'ips_condit' => $row[$columnIndexes['ips_condit']] ?? null,
            'nreligible' => $row[$columnIndexes['nreligible']] ?? null,
            'lpss' => $row[$columnIndexes['lpss']] ?? null,
            'wtr_active' => $row[$columnIndexes['wtr_active']] ?? null,
            'rni' => $this->parseInteger($row[$columnIndexes['rni']] ?? null),
            'dpw_quad' => $row[$columnIndexes['dpw_quad']] ?? null,
            'tnt_name' => $row[$columnIndexes['tnt_name']] ?? null,
            'nhood' => $row[$columnIndexes['nhood']] ?? null,
            'nrsa' => $row[$columnIndexes['nrsa']] ?? null,
            'doce_area' => $row[$columnIndexes['doce_area']] ?? null,
            'zone_dist' => $row[$columnIndexes['zone_dist_']] ?? null,
            'rezone' => $row[$columnIndexes['rezone']] ?? null,
            'new_cc_dis' => $row[$columnIndexes['new_cc_dis']] ?? null,
            'ctid_2020' => $row[$columnIndexes['ctid_2020']] ?? null,
            'ctlab_2020' => $row[$columnIndexes['ctlab_2020']] ?? null,
            'ct_2020' => $this->parseInteger($row[$columnIndexes['ct_2020']] ?? null),
            'specnhood' => $this->parseInteger($row[$columnIndexes['specnhood']] ?? null),
            'inpd' => $this->parseInteger($row[$columnIndexes['inpd']] ?? null),
            'pdname' => $row[$columnIndexes['pdname']] ?? null,
            'elect_dist' => $row[$columnIndexes['elect_dist']] ?? null,
            'city_ward' => $row[$columnIndexes['city_ward']] ?? null,
            'county_leg' => $this->parseInteger($row[$columnIndexes['county_leg']] ?? null),
            'nys_assemb' => $this->parseInteger($row[$columnIndexes['nys_assemb']] ?? null),
            'nys_senate' => $this->parseInteger($row[$columnIndexes['nys_senate']] ?? null),
            'us_congr' => $this->parseInteger($row[$columnIndexes['us_congr']] ?? null),
            'objectid_1' => $this->parseInteger($row[$columnIndexes['objectid_1']] ?? null),
            'shape_area' => $this->parseNumeric($row[$columnIndexes['shape__area']] ?? null),
            'shape_length' => $this->parseNumeric($row[$columnIndexes['shape__length']] ?? null),
            'created_at' => now(),
            'updated_at' => now()
        ];

        return $data;
    }

    private function insertBatch(array $batch)
    {
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            try {
                DB::beginTransaction();
                DB::table('parcel_maps')->insert($batch);
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

    private function parseNumeric($value)
    {
        if (empty($value) || $value === ' ') return null;
        return is_numeric($value) ? (float)$value : null;
    }

    private function parseInteger($value)
    {
        if (empty($value) || $value === ' ') return null;
        return is_numeric($value) ? (int)$value : null;
    }
}
