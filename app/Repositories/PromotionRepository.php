<?php

namespace App\Repositories;

use App\Models\Promotion;
use App\Models\Product;
use App\Contracts\Repositories\PromotionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PromotionRepository implements PromotionRepositoryInterface
{
    /**
     * Получить все акции
     */
    public function getAll(): Collection
    {
        return Promotion::all();
    }

    /**
     * Получить акцию по ID
     */
    public function findById(int $id): ?Promotion
    {
        return Promotion::find($id);
    }

    /**
     * Создать акцию
     */
    public function create(array $data): Promotion
    {
        return Promotion::create($data);
    }

    /**
     * Обновить акцию
     */
    public function update(Promotion $promotion, array $data): bool
    {
        return $promotion->update($data);
    }

    /**
     * Удалить акцию
     */
    public function delete(Promotion $promotion): bool
    {
        return $promotion->delete();
    }

    /**
     * Получить активные акции для товара
     */
    public function getActivePromotionsForProduct(int $productId): Collection
    {
        return Product::find($productId)
            ->promotions()
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
    }

    /**
     * Проверить, активна ли акция в данный момент
     */
    public function isActive(Promotion $promotion): bool
    {
        $now = now();
        return $promotion->is_active &&
               $promotion->start_date <= $now &&
               $promotion->end_date >= $now;
    }

    /**
     * Рассчитать скидку для указанной цены
     */
    public function calculateDiscount(Promotion $promotion, int $price): int
    {
        if (!$this->isActive($promotion)) {
            return 0;
        }

        return match($promotion->discount_type) {
            'percentage' => (int) round($price * $promotion->discount_value / 100),
            'fixed' => min($promotion->discount_value, $price),
            default => 0,
        };
    }
}
