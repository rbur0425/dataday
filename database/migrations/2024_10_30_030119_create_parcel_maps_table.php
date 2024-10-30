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
        Schema::create('parcel_maps', function (Blueprint $table) {
            $table->id();
            $table->integer('fid')->nullable();
            $table->integer('objectid')->nullable();
            $table->string('tax_id', 100)->nullable();
            $table->string('printkey', 100)->nullable();
            $table->string('addressnum', 100)->nullable();
            $table->string('addressnam', 255)->nullable();
            $table->decimal('lat', 15, 6)->nullable();
            $table->decimal('long', 15, 6)->nullable();
            $table->string('tax_id_1', 100)->nullable();
            $table->string('sbl', 100)->nullable();
            $table->string('pnumbr', 100)->nullable();
            $table->string('st_num', 100)->nullable();
            $table->string('st_name', 255)->nullable();
            $table->string('full_address', 255)->nullable();
            $table->string('zip', 20)->nullable();
            $table->text('desc_1')->nullable();
            $table->text('desc_2')->nullable();
            $table->text('desc_3')->nullable();
            $table->integer('shape_ind')->nullable();
            $table->string('luc_parcel', 100)->nullable();
            $table->string('lu_parcel', 255)->nullable();
            $table->string('lucat_old', 255)->nullable();
            $table->decimal('land_av', 15, 2)->nullable();
            $table->decimal('total_av', 15, 2)->nullable();
            $table->string('owner', 255)->nullable();
            $table->string('add1_ownpo', 255)->nullable();
            $table->string('add2_ownst', 255)->nullable();
            $table->string('add3_ownun', 255)->nullable();
            $table->string('add4_ownci', 255)->nullable();
            $table->decimal('front', 15, 2)->nullable();
            $table->decimal('depth', 15, 2)->nullable();
            $table->decimal('acres', 15, 2)->nullable();
            $table->integer('yr_built')->nullable();
            $table->integer('n_resunits')->nullable();
            $table->string('ipsvacant', 100)->nullable();
            $table->string('ips_condit', 100)->nullable();
            $table->string('nreligible', 100)->nullable();
            $table->string('lpss', 100)->nullable();
            $table->string('wtr_active', 255)->nullable(); // Changed to string(255)
            $table->integer('rni')->nullable();
            $table->string('dpw_quad', 100)->nullable();
            $table->string('tnt_name', 255)->nullable();
            $table->string('nhood', 255)->nullable();
            $table->string('nrsa', 255)->nullable();
            $table->string('doce_area')->nullable(); // Changed to string to handle potential non-numeric values
            $table->string('zone_dist', 100)->nullable();
            $table->string('rezone', 100)->nullable();
            $table->string('new_cc_dis', 100)->nullable();
            $table->string('ctid_2020', 100)->nullable();
            $table->string('ctlab_2020', 255)->nullable();
            $table->integer('ct_2020')->nullable();
            $table->integer('specnhood')->nullable();
            $table->integer('inpd')->nullable();
            $table->string('pdname', 255)->nullable();
            $table->string('elect_dist', 100)->nullable();
            $table->string('city_ward', 100)->nullable();
            $table->integer('county_leg')->nullable();
            $table->integer('nys_assemb')->nullable();
            $table->integer('nys_senate')->nullable();
            $table->integer('us_congr')->nullable();
            $table->integer('objectid_1')->nullable();
            $table->decimal('shape_area', 20, 10)->nullable();
            $table->decimal('shape_length', 20, 10)->nullable();
            $table->timestamps();

            // Add indexes for commonly queried fields
            $table->index('sbl');
            $table->index('tax_id');
            $table->index('full_address');
            $table->index(['lat', 'long']);
            $table->index('owner');
            $table->index('nhood');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel_maps');
    }
};
