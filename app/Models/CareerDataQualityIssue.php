<?php

namespace App\Models;

use App\Enums\DataQualityIssueType;
use App\Enums\DataQualitySeverity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerDataQualityIssue extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'issue_type' => DataQualityIssueType::class,
        'severity' => DataQualitySeverity::class,
        'context' => 'array',
    ];

    public function importRun(): BelongsTo
    {
        return $this->belongsTo(CareerImportRun::class, 'import_run_id');
    }

    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(CareerSourceDocument::class, 'source_document_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(CareerProgram::class, 'program_id');
    }

    public function careerProgram(): BelongsTo
    {
        return $this->program();
    }
}
