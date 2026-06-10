<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'code',
        'normalized_name',
        'source_url',
    ];

    /**
     * Get the academic programs for this faculty.
     */
    public function academicPrograms(): HasMany
    {
        return $this->hasMany(AcademicProgram::class);
    }
}
