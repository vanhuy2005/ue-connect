<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerContributionModerationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'career_contribution_id',
        'admin_id',
        'action',
        'reason',
        'previous_status',
        'new_status',
    ];

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(CareerContribution::class, 'career_contribution_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
