<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class ZillowRentalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $csvFile = storage_path('app/zillow/Zip_zori_uc_sfrcondomfr_sm_sa_month.csv');
        $file = fopen($csvFile, 'r');

        // Skip the header row but keep it for reference
        $headers = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $data = [
                'region_id' => $row[0],
                'size_rank' => $row[1],
                'region_name' => $row[2],
                'region_type' => $row[3],
                'state_name' => $row[4],
                'state' => $row[5],
                'city' => $row[6],
                'metro' => $row[7],
                'county_name' => $row[8],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Process price columns (starting from index 9)
            for ($i = 9; $i < count($headers); $i++) {
                if (!empty($row[$i])) {
                    // Parse the date from header (format: M/D/YY)
                    $date = Carbon::createFromFormat('n/d/y', $headers[$i]);
                    $columnName = sprintf(
                        'price_%d_%02d',
                        $date->year,
                        $date->month
                    );
                    $data[$columnName] = $row[$i];
                }
            }

            DB::table('zillow_rental_data')->insert($data);
        }

        fclose($file);
    }
}
