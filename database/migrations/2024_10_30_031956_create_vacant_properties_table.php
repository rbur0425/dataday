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
        Schema::create('vacant_properties', function (Blueprint $table) {
            $table->id();
            $table->decimal('x_coordinate', 15, 8)->nullable();
            $table->decimal('y_coordinate', 15, 8)->nullable();
            $table->string('sbl')->nullable();
            $table->string('property_address')->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('owner')->nullable();
            $table->string('owner_address')->nullable();
            $table->string('vacant_type')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('vpr_result')->nullable();
            $table->timestamp('completion_date')->nullable();
            $table->string('completion_type_name')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->string('vpr_valid', 5)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('object_id')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('sbl');
            $table->index('property_address');
            $table->index(['latitude', 'longitude']);
            $table->index('neighborhood');
            $table->index('vpr_valid');
            $table->index('vacant_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacant_properties');
    }
};
