<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'active'];

    public function config()
    {
        return $this->hasOne(WinnerConfig::class);
    }

    /**
     * Get all spins where this participant won.
     */
    public function spins()
    {
        return $this->hasMany(Spin::class);
    }
}


