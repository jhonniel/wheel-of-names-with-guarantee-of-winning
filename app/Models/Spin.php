<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spin extends Model
{
    use HasFactory;

    protected $fillable = ['participant_id', 'pool_snapshot', 'angle'];

    protected $casts = [
        'pool_snapshot' => 'array',
    ];

    /**
     * Get the participant that won this spin.
     */
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}


