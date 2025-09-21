<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Winner extends Model
{
    protected $fillable = [
        'participant_id',
        'user_id',
        'winner_name',
        'winner_color',
        'spin_angle',
        'pool_snapshot',
        'won_at',
    ];

    protected $casts = [
        'participant_id' => 'integer',
        'user_id' => 'integer',
        'pool_snapshot' => 'array',
        'won_at' => 'datetime',
        'spin_angle' => 'decimal:2',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
