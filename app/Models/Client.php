<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'unit',
        'volume',
        'unit_price',
        'total_cost',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectProgress()
    {
        return $this->hasOne(ProjectProgress::class);
    }
}
