<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerUserPathwayReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'pathway_id',
        'reason',
        'description',
        'status',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function pathway(): BelongsTo
    {
        return $this->belongsTo(CareerUserPathway::class, 'pathway_id');
    }
}
