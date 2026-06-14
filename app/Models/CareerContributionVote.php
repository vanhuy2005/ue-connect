<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerContributionVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'career_contribution_id',
        'user_id',
        'value',
    ];

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(CareerContribution::class, 'career_contribution_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
