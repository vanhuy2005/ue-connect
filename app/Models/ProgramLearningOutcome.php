<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramLearningOutcome extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'code',
        'description',
        'category',
        'source_location',
    ];

    /**
     * Get the training program.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'program_id');
    }
}
