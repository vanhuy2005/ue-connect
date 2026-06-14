<?php

namespace App\Models;

use App\Enums\CareerPositionSectionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerPositionSection extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'section_type' => CareerPositionSectionType::class,
            'metadata_json' => 'array',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(CareerPosition::class, 'position_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CareerPositionItem::class, 'section_id')->orderBy('order_index');
    }
}
