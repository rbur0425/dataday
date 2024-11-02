<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('zillow_rental_data', function (Blueprint $table) {
            $table->id();
            $table->integer('region_id');
            $table->integer('size_rank');
            $table->string('region_name');
            $table->string('region_type');
            $table->string('state_name');
            $table->string('state');
            $table->string('city');
            $table->string('metro');
            $table->string('county_name');

            // Add a column for each month
            // Starting from January 2015 to September 2024
            foreach (range(2015, 2024) as $year) {
                foreach (range(1, 12) as $month) {
                    // Skip months after September 2024
                    if ($year == 2024 && $month > 9) continue;

                    $columnName = sprintf('price_%d_%02d', $year, $month);
                    $table->decimal($columnName, 10, 2)->nullable();
                }
            }

            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('zillow_rental_data');
    }
};
