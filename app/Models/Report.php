<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_name',
        'area',
        'works_title',
        'total_cost_exclude_ppn',
        'total_cost_rounded',
    ];

    public function works()
    {
        return $this->hasMany(Work::class);
    }
}
