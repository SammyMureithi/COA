<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestResults extends Model
{
    protected $taable="test_results";
    protected $fillable=['batch_number','sample_number'];
}
