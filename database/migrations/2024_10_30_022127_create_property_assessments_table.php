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
        Schema::create('property_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('sbl');  // Changed to lowercase
            $table->string('property_address')->nullable();  // Changed to lowercase
            $table->string('property_city')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('property_class')->nullable();
            $table->string('prop_class_description')->nullable();
            $table->string('primary_owner')->nullable();
            $table->string('secondary_owner')->nullable();
            $table->string('owner_address')->nullable();
            $table->string('po_box')->nullable();
            $table->decimal('school_taxable', 15, 2)->nullable();
            $table->decimal('municipality_taxable', 15, 2)->nullable();
            $table->decimal('county_taxable', 15, 2)->nullable();
            $table->decimal('total_assessment', 15, 2)->nullable();
            $table->string('exemption_1_description')->nullable();
            $table->decimal('exemption_1_amt', 15, 2)->nullable();
            $table->string('exemption_2_description')->nullable();
            $table->decimal('exemption_2_amt', 15, 2)->nullable();
            $table->string('exemption_3_description')->nullable();
            $table->decimal('exemption_3_amt', 15, 2)->nullable();
            $table->string('exemption_4_description')->nullable();
            $table->decimal('exemption_4_amt', 15, 2)->nullable();
            $table->string('exemption_5_description')->nullable();
            $table->decimal('exemption_5_amt', 15, 2)->nullable();
            $table->string('exemption_6_description')->nullable();
            $table->decimal('exemption_6_amt', 15, 2)->nullable();
            $table->integer('object_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_assessments');
    }
};
