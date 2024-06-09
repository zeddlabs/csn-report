<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectProgress extends Model
{
    use HasFactory;

    protected $table = 'project_progresses';

    protected $fillable = [
        'client_id',
        'total_progress',
        'construction_cost',
        'ppn',
        'total_construction_cost',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function works()
    {
        return $this->hasMany(Work::class);
    }
}
