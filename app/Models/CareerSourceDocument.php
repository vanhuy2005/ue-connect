<?php

namespace App\Models;

use App\Enums\SourceDocumentType;
use App\Enums\SourceExtractionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerSourceDocument extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'document_type' => SourceDocumentType::class,
        'extraction_status' => SourceExtractionStatus::class,
    ];

    public function importRun(): BelongsTo
    {
        return $this->belongsTo(CareerImportRun::class, 'import_run_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(CareerProgram::class, 'source_document_id');
    }

    public function programCourses(): HasMany
    {
        return $this->hasMany(CareerProgramCourse::class, 'source_document_id');
    }
}
