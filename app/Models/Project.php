<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'area',
        'total_cost_exclude_ppn',
        'total_cost_rounded',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
