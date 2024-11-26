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
        Schema::create('export_data', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->date('date')->nullable(); // DATE
            $table->string('hs_code')->nullable(); // HS_CODE
            $table->string('product_description')->nullable(); // PRODUCT_DESCRIPTION
            $table->decimal('quantity', 15, 2)->nullable(); // QUANTITY
            $table->string('unit')->nullable(); // UNIT
            $table->decimal('fob_value_inr', 15, 2)->nullable(); // FOB_VALUE_INR
            $table->decimal('unit_price_inr', 15, 2)->nullable(); // UNIT_PRICE_INR
            $table->decimal('fob_value_usd', 15, 2)->nullable(); // FOB_VALUE_USD
            $table->decimal('fob_value_foreign_currency', 15, 2)->nullable(); // FOB_VALUE_FOREIGN_CURRENCY
            $table->decimal('unit_price_foreign_currency', 15, 2)->nullable(); // UNIT_PRICE_FOREIGN_CURRENCY
            $table->string('currency_name')->nullable(); // CURRENCY_NAME
            $table->decimal('fob_value_in_lacs_inr', 15, 2)->nullable(); // FOB_VALUE_IN_LACS_INR
            $table->string('iec')->nullable(); // IEC
            $table->string('indian_exporter_name')->nullable(); // INDIAN_EXPORTER_NAME
            $table->string('exporter_address')->nullable(); // EXPORTER_ADDRESS
            $table->string('exporter_city')->nullable(); // EXPORTER_CITY
            $table->string('pin_code')->nullable(); // PIN_CODE
            $table->string('cha_name')->nullable(); // CHA_NAME
            $table->string('foreign_importer_name')->nullable(); // FOREIGN_IMPORTER_NAME
            $table->string('importer_address')->nullable(); // IMPORTER_ADDRESS
            $table->string('importer_country')->nullable(); // IMPORTER_COUNTRY
            $table->string('foreign_port')->nullable(); // FOREIGN_PORT
            $table->string('foreign_country')->nullable(); // FOREIGN_COUNTRY
            $table->string('indian_port')->nullable(); // INDIAN_PORT
            $table->string('item_no')->nullable(); // ITEM_NO
            $table->decimal('drawback', 15, 2)->nullable(); // DRAWBACK
            $table->string('chapter')->nullable(); // CHAPTER
            $table->string('hs_4_digit')->nullable(); // HS_4_DIGIT
            $table->string('month')->nullable(); // MONTH
            $table->year('year')->nullable(); // YEAR
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_data');
    }
};
