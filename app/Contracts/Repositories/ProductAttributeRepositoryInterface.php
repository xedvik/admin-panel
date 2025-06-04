<?php

namespace App\Contracts\Repositories;

use App\Models\ProductAttribute;
use Illuminate\Support\Collection;

interface ProductAttributeRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить активные атрибуты отсортированные
     */
    public function getActive(): Collection;

    /**
     * Получить атрибуты с опциями для select
     */
    public function getSelectOptions(int $attributeId): array;

    /**
     * Найти по slug
     */
    public function findBySlug(string $slug): ?ProductAttribute;

    /**
     * Получить атрибуты с количеством значений
     */
    public function getWithValuesCount(): Collection;
}
