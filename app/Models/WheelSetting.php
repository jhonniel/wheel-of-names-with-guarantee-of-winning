<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WheelSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set a setting value by key
     */
    public static function setValue($key, $value)
    {
        return static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }

    /**
     * Get spin duration setting
     */
    public static function getSpinDuration()
    {
        return (float) static::getValue('spin_duration', 1);
    }

    /**
     * Set spin duration setting
     */
    public static function setSpinDuration($duration)
    {
        return static::setValue('spin_duration', $duration);
    }
}
