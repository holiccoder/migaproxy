<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    /** @use HasFactory<\Database\Factories\SystemSettingFactory> */
    use HasFactory;

    public const KEY_FRONTEND_LOGO_PATH = 'frontend_logo_path';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getString(string $key, ?string $default = null): ?string
    {
        $value = static::query()
            ->where('key', $key)
            ->value('value');

        if (! is_string($value) || $value === '') {
            return $default;
        }

        return $value;
    }

    public static function putString(string $key, ?string $value): void
    {
        if (! is_string($value) || trim($value) === '') {
            static::query()->where('key', $key)->delete();

            return;
        }

        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => trim($value)],
        );
    }
}
