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
        Schema::create('code_violations', function (Blueprint $table) {
            $table->id();
            $table->decimal('x_coordinate', 15, 8)->nullable();
            $table->decimal('y_coordinate', 15, 8)->nullable();
            $table->string('violation_number');
            $table->string('complaint_address')->nullable();
            $table->string('complaint_zip', 10)->nullable();
            $table->string('sbl')->nullable();
            $table->text('violation')->nullable();
            $table->timestamp('violation_date')->nullable();
            $table->timestamp('comply_by_date')->nullable();
            $table->string('status_type_name')->nullable();
            $table->string('complaint_number')->nullable();
            $table->string('complaint_type_name')->nullable();
            $table->timestamp('open_date')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('inspector_id')->nullable();
            $table->string('neighborhood')->nullable();
            $table->boolean('vacant')->nullable();
            $table->string('owner_address')->nullable();
            $table->string('owner_city')->nullable();
            $table->string('owner_state', 2)->nullable();
            $table->string('owner_zip_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('object_id')->nullable();
            $table->timestamps();

            // Add indexes for commonly queried fields
            $table->index('violation_number');
            $table->index('sbl');
            $table->index('status_type_name');
            $table->index('complaint_number');
            $table->index('violation_date');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_violations');
    }
};
