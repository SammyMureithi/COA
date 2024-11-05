<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestResults extends Model
{
    protected $table="test_results";
    protected $fillable=['batch_number','sample_number','aceton_insoluble',
    'acid_value','color_gardner','peroxide_value','result_based_on_sample_mass',
    'toluene_insoluble_matter','viscosity_25C'];
}
