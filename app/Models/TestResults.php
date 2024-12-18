<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestResults extends Model
{
    protected $table="test_results";
    protected $fillable=[ 
        'sample_number',
    'batch_number',
    'aceton_insoluble',
    'acid_value',
    'color_gardner',
    'peroxide_value',
    'result_based_on_sample_mass',
    'toluene_insoluble_matter',
    'viscosity_25C',
    'moisture',
    'total_plate_count',
    'arsenic',
    'cadmium',
    'lead',
    'mercury',
    'iron', 
   'GMO_Screening' ,
   "enterobacteriaceae" ,
   "yeasts_and_moulds",
   "yeasts",
   "moulds" ,
   'lpc',
   'phosphorous',
   'pesticide_status'
];
}
