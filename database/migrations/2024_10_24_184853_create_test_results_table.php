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
            $table->string('batch_number');
            $table->string('sample_number'); 
            $table->string('aceton_insoluble');
            $table->string('acid_value');
            $table->string('color_gardner');
            $table->string('peroxide_value');
            $table->string('result_based_on_sample_mass');
            $table->string('toluene_insoluble_matter');
            $table->string('viscosity_25C');
            $table->unique(['batch_number', 'sample_number']);
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