<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'name',
        'unit',
        'volume',
        'unit_price',
        'total_price',
    ];
}
