<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'community_members';

    protected $fillable = [
        'community_id',
        'user_id',
        'role',
        'joined_at',
        'status',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
