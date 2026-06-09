<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionCohort extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'cohort_name',
        'normalized_name',
        'note',
    ];

    /**
     * Get the training programs for this cohort.
     */
    public function trainingPrograms(): HasMany
    {
        return $this->hasMany(TrainingProgram::class, 'cohort_id');
    }
}
