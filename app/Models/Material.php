<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'name',
        'unit',
        'quantity_plan',
        'unit_price',
        'quantity_progress',
        'total_price',
        'weight_factory',
    ];
}
