<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WheelBackground extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the active background
     */
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Set this background as active and deactivate others
     */
    public function setActive()
    {
        static::where('is_active', true)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }
}
