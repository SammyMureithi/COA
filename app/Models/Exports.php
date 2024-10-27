<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exports extends Model
{
    protected $taable="exports";
    protected $fillable=['hs_code','date','product_description','quantity','unit',
    'fob_value_usd','indian_export_name','foreign_export_name','importer_country'];
}
