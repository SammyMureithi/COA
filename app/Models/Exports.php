<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exports extends Model
{
    protected $taable="exports";
    protected $fillable=[ 
    //     'hs_code',
    // 'date',
    // 'quantity',
    // 'unit',
    // 'fob_value_usd',
   
    // 'foreign_export_name',

    'product_description',
    'indian_exporter_name',
    'importer_country',
    'red equivalent (if any; if not (certain): undefined)'
];
}
