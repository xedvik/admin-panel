<?php

namespace App\Services;

use App\Contracts\Repositories\PromotionRepositoryInterface;

class PromotionService
{
    public function __construct(
        private PromotionRepositoryInterface $promotionRepository
    ) {}

    /**
     * Получить текущую активную акцию для товара
     */
    public function getActivePromotionForProduct(int $productId): ?array
    {
        $promotion = $this->promotionRepository->getActivePromotionsForProduct($productId)->first();
        return $promotion ? $promotion->toArray() : null;
    }

    /**
     * Получить цену товара со скидкой
     */
    public function getDiscountedPrice(int $productId, int $price): int
    {
        $promotion = $this->promotionRepository->getActivePromotionsForProduct($productId)->first();

        if (!$promotion) {
            return $price;
        }

        $discount = $this->promotionRepository->calculateDiscount($promotion, $price);
        return $price - $discount;
    }

    /**
     * Создать акцию
     */
    public function createPromotion(array $data): array
    {
        return $this->promotionRepository->create($data)->toArray();
    }

    /**
     * Обновить акцию
     */
    public function updatePromotion(int $promotionId, array $data): bool
    {
        $promotion = $this->promotionRepository->findById($promotionId);
        if (!$promotion) {
            return false;
        }
        return $this->promotionRepository->update($promotion, $data);
    }

    /**
     * Удалить акцию
     */
    public function deletePromotion(int $promotionId): bool
    {
        $promotion = $this->promotionRepository->findById($promotionId);
        if (!$promotion) {
            return false;
        }
        return $this->promotionRepository->delete($promotion);
    }
}
