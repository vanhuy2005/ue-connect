<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerUserPathwayComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pathway_id',
        'user_id',
        'parent_id',
        'body',
        'status',
    ];

    public function pathway(): BelongsTo
    {
        return $this->belongsTo(CareerUserPathway::class, 'pathway_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
