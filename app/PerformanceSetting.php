<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerformanceSetting extends Model
{
    protected $table = 'performance_settings';
    protected $guarded = ['id'];

    /**
     * Ambil nilai konfigurasi Performance Management dengan fallback default.
     */
    public static function getValue(string $key, $default = null)
    {
        try {
            $row = static::where('setting_key', $key)->first();
            return $row ? $row->setting_value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Simpan atau perbarui nilai konfigurasi Performance Management.
     */
    public static function setValue(string $key, $value, ?int $userId = null, ?string $description = null): self
    {
        $attrs = [
            'setting_value' => $value,
            'updated_by'    => $userId,
        ];
        if ($description !== null) {
            $attrs['description'] = $description;
        }

        return static::updateOrCreate(['setting_key' => $key], $attrs);
    }
}
