<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportData extends Model
{
    use HasFactory;

    protected $table = 'export_data'; // Table name (optional if it follows convention)

    // Fillable fields for mass assignment
    protected $fillable = [
        'date',
        'hs_code',
        'product_description',
        'quantity',
        'unit',
        'fob_value_inr',
        'unit_price_inr',
        'fob_value_usd',
        'fob_value_foreign_currency',
        'unit_price_foreign_currency',
        'currency_name',
        'fob_value_in_lacs_inr',
        'iec',
        'indian_exporter_name',
        'exporter_address',
        'exporter_city',
        'pin_code',
        'cha_name',
        'foreign_importer_name',
        'importer_address',
        'importer_country',
        'foreign_port',
        'foreign_country',
        'indian_port',
        'item_no',
        'drawback',
        'chapter',
        'hs_4_digit',
        'month',
        'year',
    ];
}
