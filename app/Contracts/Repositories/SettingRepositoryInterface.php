<?php

namespace App\Contracts\Repositories;

use App\Models\Setting;

interface SettingRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить значение настройки по ключу
     */
    public function getValue(string $key, mixed $default = null): mixed;

    /**
     * Установить значение настройки
     */
    public function setValue(string $key, mixed $value, string $type = 'string', array $meta = []): Setting;

    /**
     * Получить все публичные настройки
     */
    public function getPublicSettings(): array;

    /**
     * Получить настройки по группе
     */
    public function getByGroup(string $group): array;

    /**
     * Удалить настройку
     */
    public function removeSetting(string $key): bool;

    /**
     * Очистить кеш настроек
     */
    public function clearCache(): void;

    /**
     * Найти настройку по ключу
     */
    public function findByKey(string $key): ?Setting;

    /**
     * Получить список всех групп настроек
     */
    public function getGroups(): array;
}
