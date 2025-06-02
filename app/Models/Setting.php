<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Получить значение настройки по ключу
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Установить значение настройки
     */
    public static function set(string $key, mixed $value, string $type = 'string', array $meta = []): Setting
    {
        $stringValue = self::valueToString($value, $type);

        $setting = self::updateOrCreate(
            ['key' => $key],
            array_merge([
                'value' => $stringValue,
                'type' => $type,
            ], $meta)
        );

        // Очищаем кеш
        Cache::forget("setting_{$key}");
        Cache::forget('all_public_settings');

        return $setting;
    }

    /**
     * Получить все публичные настройки для API
     */
    public static function getPublic(): array
    {
        return Cache::remember('all_public_settings', 3600, function () {
            $settings = self::where('is_public', true)->get();

            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = self::castValue($setting->value, $setting->type);
            }

            return $result;
        });
    }

    /**
     * Получить настройки по группе
     */
    public static function getByGroup(string $group): array
    {
        $settings = self::where('group', $group)->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = self::castValue($setting->value, $setting->type);
        }

        return $result;
    }

    /**
     * Удалить настройку
     */
    public static function remove(string $key): bool
    {
        $setting = self::where('key', $key)->first();

        if ($setting) {
            Cache::forget("setting_{$key}");
            Cache::forget('all_public_settings');
            return $setting->delete();
        }

        return false;
    }

    /**
     * Очистить весь кеш настроек
     */
    public static function clearCache(): void
    {
        $keys = self::pluck('key');

        foreach ($keys as $key) {
            Cache::forget("setting_{$key}");
        }

        Cache::forget('all_public_settings');
    }

    /**
     * Привести значение к нужному типу
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            'float' => (float) $value,
            default => (string) $value,
        };
    }

    /**
     * Преобразовать значение в строку для хранения
     */
    protected static function valueToString(mixed $value, string $type): string
    {
        if ($value === null) {
            return '';
        }

        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE),
            default => (string) $value,
        };
    }

    /**
     * Получить accessor для правильного cast'а значения
     */
    public function getValueAttribute($value): mixed
    {
        return self::castValue($value, $this->type);
    }

    /**
     * Scopes для удобной фильтрации
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        // Очищаем кеш при изменении настроек
        static::saved(function ($setting) {
            Cache::forget("setting_{$setting->key}");
            Cache::forget('all_public_settings');
        });

        static::deleted(function ($setting) {
            Cache::forget("setting_{$setting->key}");
            Cache::forget('all_public_settings');
        });
    }
}
