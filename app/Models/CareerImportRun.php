<?php

namespace App\Models;

use App\Enums\ImportRunStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerImportRun extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => ImportRunStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function sourceDocuments(): HasMany
    {
        return $this->hasMany(CareerSourceDocument::class, 'import_run_id');
    }

    public function dataQualityIssues(): HasMany
    {
        return $this->hasMany(CareerDataQualityIssue::class, 'import_run_id');
    }
}
