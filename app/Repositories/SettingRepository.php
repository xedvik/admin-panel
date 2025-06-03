<?php

namespace App\Repositories;

use App\Contracts\Repositories\SettingRepositoryInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    public function getValue(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = $this->findByKey($key);

            if (!$setting) {
                return $default;
            }

            return $this->castValue($setting->value, $setting->type);
        });
    }

    public function setValue(string $key, mixed $value, string $type = 'string', array $meta = []): Setting
    {
        $stringValue = $this->valueToString($value, $type);

        $setting = $this->model->updateOrCreate(
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

    public function getPublicSettings(): array
    {
        return Cache::remember('all_public_settings', 3600, function () {
            $settings = $this->model->where('is_public', true)->get();

            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = $this->castValue($setting->value, $setting->type);
            }

            return $result;
        });
    }

    public function getByGroup(string $group): array
    {
        $settings = $this->model->where('group', $group)->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $this->castValue($setting->value, $setting->type);
        }

        return $result;
    }

    public function removeSetting(string $key): bool
    {
        $setting = $this->findByKey($key);

        if ($setting) {
            Cache::forget("setting_{$key}");
            Cache::forget('all_public_settings');
            return $setting->delete();
        }

        return false;
    }

    public function clearCache(): void
    {
        $keys = $this->model->pluck('key');

        foreach ($keys as $key) {
            Cache::forget("setting_{$key}");
        }

        Cache::forget('all_public_settings');
    }

    public function findByKey(string $key): ?Setting
    {
        return $this->model->where('key', $key)->first();
    }

    /**
     * Привести значение к нужному типу
     */
    protected function castValue(mixed $value, string $type): mixed
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
    protected function valueToString(mixed $value, string $type): string
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
     * Получить настройки по типу
     */
    // public function getByType(string $type): array
    // {
    //     $settings = $this->model->where('type', $type)->get();

    //     $result = [];
    //     foreach ($settings as $setting) {
    //         $result[$setting->key] = $this->castValue($setting->value, $setting->type);
    //     }

    //     return $result;
    // }

    /**
     * Массовое обновление настроек
     */
    // public function bulkUpdate(array $settings): array
    // {
    //     $results = [];

    //     foreach ($settings as $key => $value) {
    //         $type = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : (is_array($value) ? 'json' : 'string'));
    //         $results[$key] = $this->setValue($key, $value, $type);
    //     }

    //     return $results;
    // }

    /**
     * Получить список всех групп настроек
     */
    public function getGroups(): array
    {
        return $this->model->whereNotNull('group')
            ->distinct()
            ->pluck('group', 'group')
            ->toArray();
    }
}
