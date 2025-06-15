<?php

namespace App\Repositories;

use App\Contracts\Repositories\PromotionRepositoryInterface;
use App\Models\Promotion;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class PromotionRepository implements PromotionRepositoryInterface
{
    public function __construct(
        private Promotion $model
    ) {}

    /**
     * Получить все акции
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Получить акцию по ID
     */
    public function findById(int $id): ?Promotion
    {
        return $this->model->find($id);
    }

    /**
     * Создать акцию
     */
    public function create(array $data): Promotion
    {
        return $this->model->create($data);
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
        $now = now();
        return $this->model->whereHas('products', fn($q) => $q->where('products.id', $productId))
            ->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->get();
    }

    /**
     * Получить активную акцию для товара
     */
    public function getActivePromotionForProduct(Product $product): ?Promotion
    {
        $now = now();
        return $product->promotions()
            ->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->first();
    }

    /**
     * Проверить, активна ли акция в данный момент
     */
    public function isActive(Promotion $promotion): bool
    {
        if (!$promotion->is_active) {
            return false;
        }

        $now = now();
        return $promotion->start_date <= $now && $promotion->end_date >= $now;
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
