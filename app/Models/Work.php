<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_progress_id',
        'work_type_id',
        'name',
    ];

    public function projectProgress()
    {
        return $this->belongsTo(ProjectProgress::class);
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }
}
