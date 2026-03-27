<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    /** @use HasFactory<\Database\Factories\SystemSettingFactory> */
    use HasFactory;

    public const KEY_FRONTEND_LOGO_PATH = 'frontend_logo_path';

    public const KEY_FRONTEND_FAVICON_PATH = 'frontend_favicon_path';

    public const KEY_DEFAULT_AFFILIATE_COMMISSION_RATE = 'default_affiliate_commission_rate';

    public const DEFAULT_AFFILIATE_COMMISSION_RATE = 10;

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
