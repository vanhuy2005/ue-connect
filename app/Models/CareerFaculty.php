<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerFaculty extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function majors(): HasMany
    {
        return $this->hasMany(CareerMajor::class, 'faculty_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(CareerProgram::class, 'faculty_id');
    }
}
