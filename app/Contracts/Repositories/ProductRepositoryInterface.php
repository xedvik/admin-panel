<?php

namespace App\Contracts\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить только активные товары
     */
    public function getActive(): Collection;


    /**
     * Получить только рекомендуемые товары
     */
    public function getFeatured(): Collection;

    /**
     * Получить товары в наличии
     */
    public function getInStock(): Collection;


    /**
     * Найти товар по SKU
     */
    public function findBySku(string $sku): ?Product;

    /**
     * Найти товар по slug
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Поиск товаров по названию
     */
    public function searchByName(string $search): Collection;

    /**
     * Проверить есть ли товар в наличии
     */
    public function isInStock(int $productId): bool;

    /**
     * Обновить остатки товара
     */
    public function updateStock(int $productId, int $quantity): Product;

    /**
     * Уменьшить остатки товара
     */
    public function decrementStock(int $productId, int $quantity): Product;

    /**
     * Увеличить остатки товара
     */
    public function incrementStock(int $productId, int $quantity): Product;

    /**
     * Получить товары с низкими остатками
     */
    public function getLowStockProducts(int $threshold = 10): Collection;

    /**
     * Получить товары без остатков
     */
    public function getOutOfStockProducts(): Collection;

    /**
     * Получить query builder для товаров
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder;

    /**
     * Вычислить процент скидки для товара
     */
    public function calculateDiscountPercent(Product $product): int;

    /**
     * Получить главное изображение товара
     */
    public function getMainImage(Product $product): ?string;

    /**
     * Получить статус остатков товара
     */
    public function getStockStatus(Product $product): string;

    /**
     * Проверить доступность товара в наличии
     */
    public function checkInStock(Product $product): bool;

    public function getPopularProducts(int $limit = 10): Collection;

    /**
     * Получить товары с атрибутами
     */
    public function getWithAttributes(): Collection;

    /**
     * Получить товары по значению атрибута
     */
    public function getByAttributeValue(int $attributeId, string $value): Collection;

    /**
     * Получить уникальные значения атрибута для товаров в категории
     */
    public function getAttributeValuesForCategory(int $categoryId, int $attributeId): Collection;

    /**
     * Фильтровать товары по атрибутам
     */
    public function filterByAttributes(array $attributeFilters): Collection;

    /**
     * Синхронизировать атрибуты товара
     */
    public function syncAttributes(int $productId, array $attributes): bool;

    /**
     * Удалить все атрибуты товара
     */
    public function clearAttributes(int $productId): bool;

    /**
     * Обновить итоговую цену товара с учетом акций
     */
    public function updateFinalPrice(Product $product): void;
}
