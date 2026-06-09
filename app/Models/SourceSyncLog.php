<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourceSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_type',
        'source_url',
        'status',
        'started_at',
        'finished_at',
        'records_found',
        'records_created',
        'records_updated',
        'error_message',
        'metadata_json',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'metadata_json' => 'array',
    ];
}
