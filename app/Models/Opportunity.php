<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    use HasFactory;

    protected $primaryKey = 'post_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'post_id',
        'is_expired',
        'category',
    ];

    protected $casts = [
        'post_id' => 'integer',
        'is_expired' => 'boolean',
    ];

    /**
     * Get the post that owns the opportunity details.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
