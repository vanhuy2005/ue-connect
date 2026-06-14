<?php

namespace App\Models;

use App\Enums\CareerPositionImportanceLevel;
use App\Enums\CareerPositionItemType;
use App\Enums\CareerPositionSourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CareerPositionItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'item_type' => CareerPositionItemType::class,
            'importance_level' => CareerPositionImportanceLevel::class,
            'source_type' => CareerPositionSourceType::class,
            'metadata_json' => 'array',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(CareerPosition::class, 'position_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CareerPositionSection::class, 'section_id');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
