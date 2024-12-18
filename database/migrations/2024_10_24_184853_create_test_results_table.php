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
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->string('sample_number');
            $table->string('batch_number');
            $table->string('aceton_insoluble')->nullable();
            $table->string('acid_value')->nullable();
            $table->string('color_gardner')->nullable();
            $table->string('peroxide_value')->nullable();
            $table->string('result_based_on_sample_mass')->nullable();
            $table->string('toluene_insoluble_matter')->nullable();
            $table->string('viscosity_25C')->nullable();
            $table->string('moisture')->nullable();
            $table->string('total_plate_count')->nullable();
            $table->string('arsenic')->nullable();
            $table->string('cadmium')->nullable();
            $table->string('lead')->nullable();
            $table->string('mercury')->nullable();
            $table->string('iron')->nullable();
            $table->string('GMO_Screening')->nullable();
            $table->string('enterobacteriaceae')->nullable();
            $table->string('yeasts_and_moulds')->nullable();
            $table->string('yeasts')->nullable();
            $table->string('moulds')->nullable();
            $table->string('phosphorous')->nullable();
            $table->string('lpc')->nullable();
            $table->string('pesticide_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
