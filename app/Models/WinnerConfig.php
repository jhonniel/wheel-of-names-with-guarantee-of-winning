<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinnerConfig extends Model
{
    use HasFactory;

    protected $fillable = ['participant_id', 'guarantee_quota', 'weight', 'valid_from', 'valid_to'];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}


