<?php

namespace App\Contracts\Repositories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Product;

interface PromotionRepositoryInterface
{
    /**
     * Получить все акции
     */
    public function getAll(): Collection;

    /**
     * Получить акцию по ID
     */
    public function findById(int $id): ?Promotion;

    /**
     * Создать акцию
     */
    public function create(array $data): Promotion;

    /**
     * Обновить акцию
     */
    public function update(Promotion $promotion, array $data): bool;

    /**
     * Удалить акцию
     */
    public function delete(Promotion $promotion): bool;

    /**
     * Получить активные акции для товара
     */
    public function getActivePromotionsForProduct(int $productId): Collection;

    /**
     * Получить активную акцию для товара
     */
    public function getActivePromotionForProduct(Product $product): ?Promotion;

    /**
     * Проверить, активна ли акция в данный момент
     */
    public function isActive(Promotion $promotion): bool;

    /**
     * Рассчитать скидку для указанной цены
     */
    public function calculateDiscount(Promotion $promotion, int $price): int;
}
