<?php

namespace App\Models;

use App\Enums\CareerUserPathwayItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CareerUserPathwayItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pathway_id',
        'item_type',
        'target_type',
        'target_id',
        'semester_number',
        'title',
        'note',
        'order_index',
        'metadata_json',
    ];

    protected $casts = [
        'item_type' => CareerUserPathwayItemType::class,
        'metadata_json' => 'array',
    ];

    public function pathway(): BelongsTo
    {
        return $this->belongsTo(CareerUserPathway::class, 'pathway_id');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
