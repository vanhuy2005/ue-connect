<?php

namespace App\Models;

use App\Enums\CareerPositionStatus;
use App\Enums\CareerPositionVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerPosition extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => CareerPositionStatus::class,
            'visibility' => CareerPositionVisibility::class,
            'published_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(CareerFaculty::class, 'related_faculty_id');
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(CareerMajor::class, 'related_major_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(CareerProgram::class, 'related_program_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CareerPositionSection::class, 'position_id')->orderBy('order_index');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CareerPositionItem::class, 'position_id')->orderBy('order_index');
    }

    public function saves(): HasMany
    {
        return $this->hasMany(CareerPositionSave::class, 'position_id');
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'target');
    }
}
