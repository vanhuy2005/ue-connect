<?php

namespace App\Models;

use App\Enums\ConnectionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Connection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'status',
        'source_greeting_id',
        'connected_at',
        'disconnected_at',
    ];

    protected function casts(): array
    {
        return [
            'user_one_id' => 'integer',
            'user_two_id' => 'integer',
            'source_greeting_id' => 'integer',
            'status' => ConnectionStatus::class,
            'connected_at' => 'datetime',
            'disconnected_at' => 'datetime',
        ];
    }

    /**
     * Get user one.
     *
     * @return BelongsTo<User, $this>
     */
    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    /**
     * Get user two.
     *
     * @return BelongsTo<User, $this>
     */
    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    /**
     * Get source greeting.
     *
     * @return BelongsTo<Greeting, $this>
     */
    public function sourceGreeting(): BelongsTo
    {
        return $this->belongsTo(Greeting::class, 'source_greeting_id');
    }
}
