<?php

namespace App\Contracts\Repositories;

use App\Models\ProductAttributeValue;
use Illuminate\Support\Collection;

interface ProductAttributeValueRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить значения атрибутов для товара
     */
    public function getByProduct(int $productId): Collection;

    /**
     * Получить значения по атрибуту
     */
    public function getByAttribute(int $attributeId): Collection;

    /**
     * Обновить или создать значение атрибута для товара
     */
    public function updateOrCreateForProduct(int $productId, int $attributeId, string $value): ProductAttributeValue;

    /**
     * Удалить все значения атрибутов для товара
     */
    public function deleteByProduct(int $productId): bool;

    /**
     * Получить товары с определенным значением атрибута
     */
    public function getProductsByAttributeValue(int $attributeId, string $value): Collection;
}
