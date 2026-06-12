<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserContentPreference extends Model
{
    protected $fillable = [
        'user_id',
        'prioritize_academic_content',
        'hide_reported_content',
        'reduce_sensitive_content',
    ];

    protected $casts = [
        'prioritize_academic_content' => 'boolean',
        'hide_reported_content' => 'boolean',
        'reduce_sensitive_content' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
