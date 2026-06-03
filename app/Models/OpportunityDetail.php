<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'company',
        'position',
        'location',
        'application_url',
        'application_deadline',
        'field_tags',
        'is_expired',
        'expired_at',
    ];

    protected $casts = [
        'field_tags' => 'array',
        'is_expired' => 'boolean',
        'application_deadline' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
