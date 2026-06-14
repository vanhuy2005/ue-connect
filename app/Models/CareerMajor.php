<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerMajor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(CareerFaculty::class, 'faculty_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(CareerProgram::class, 'major_id');
    }
}
