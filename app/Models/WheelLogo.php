<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WheelLogo extends Model
{
    protected $fillable = [
        'name',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the active logo
     */
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Set this logo as active (deactivates others)
     */
    public function setActive()
    {
        // Deactivate all other logos
        static::where('id', '!=', $this->id)->update(['is_active' => false]);

        // Activate this logo
        $this->update(['is_active' => true]);
    }
}
