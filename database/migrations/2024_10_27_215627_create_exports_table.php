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
        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->string('hs_code')->nullable(); // Add a column for HS code
            $table->date('date')->nullable(); // Date of export
            $table->string('product_description')->nullable(); // Product description
            $table->integer('quantity')->nullable(); // Quantity of products
            $table->string('unit')->nullable(); // Unit of measure (e.g., kg, liters)
            $table->decimal('fob_value_usd', 15, 2)->nullable(); // FOB value in USD, up to 15 digits with 2 decimals
            $table->string('indian_export_name')->nullable(); // Name of the Indian exporter
            $table->string('foreign_export_name')->nullable(); // Name of the foreign exporter
            $table->string('importer_country')->nullable(); // Country of the importer
            $table->timestamps(); // Created at and updated at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};
