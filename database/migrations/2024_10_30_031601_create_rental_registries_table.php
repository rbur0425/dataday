<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rental_registries', function (Blueprint $table) {
            $table->id();
            $table->decimal('x_coordinate', 15, 8)->nullable();
            $table->decimal('y_coordinate', 15, 8)->nullable();
            $table->string('sbl')->nullable();
            $table->string('property_address')->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('needs_rr', 5)->nullable();
            $table->timestamp('inspect_period')->nullable();
            $table->string('completion_type_name')->nullable();
            $table->timestamp('completion_date')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->string('rr_is_valid', 5)->nullable();
            $table->timestamp('rr_app_received')->nullable();
            $table->timestamp('rr_ext_insp_pass')->nullable();
            $table->timestamp('rr_ext_insp_fail')->nullable();
            $table->timestamp('rr_int_insp_fail')->nullable();
            $table->timestamp('rr_int_insp_pass')->nullable();
            $table->string('rr_contact_name')->nullable();
            $table->string('pc_owner')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('shape')->nullable();
            $table->integer('object_id')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('sbl');
            $table->index('property_address');
            $table->index(['latitude', 'longitude']);
            $table->index('rr_is_valid');
            $table->index('valid_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_registries');
    }
};
