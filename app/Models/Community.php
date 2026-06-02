<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Community extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'status', 'created_by', 'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner()
    {
        return $this->creator();
    }

    public function members()
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function membersActive()
    {
        return $this->members()->where('status', 'active');
    }
}
