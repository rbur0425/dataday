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
        Schema::create('permit_requests', function (Blueprint $table) {
            $table->id();
            $table->string('permit_number');
            $table->string('full_address')->nullable();
            $table->string('owner')->nullable();
            $table->timestamp('issue_date')->nullable();
            $table->string('permit_type')->nullable();
            $table->text('description_of_work')->nullable();
            $table->integer('object_id')->nullable();
            $table->timestamps();

            // Add indexes for commonly queried fields
            $table->index('permit_number');
            $table->index('issue_date');
            $table->index('permit_type');
            $table->index('full_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_requests');
    }
};
