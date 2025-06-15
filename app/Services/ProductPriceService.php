<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\PromotionRepository;

class ProductPriceService
{
    public function __construct(
        private PromotionRepository $promotionRepository
    ) {}

    /**
     * Рассчитать итоговую цену товара с учетом акции
     */
    public function calculateFinalPrice(Product $product): int
    {
        // Получаем активную акцию для товара
        $promotion = $this->promotionRepository->getActivePromotionForProduct($product);

        // Если нет активной акции, итоговая цена равна обычной цене
        if (!$promotion) {
            return $product->price;
        }

        // Рассчитываем скидку
        $discount = $this->promotionRepository->calculateDiscount($promotion, $product->price);

        // Возвращаем цену с учетом скидки
        return $product->price - $discount;
    }

    /**
     * Обновить итоговую цену товара
     */
    public function updateProductFinalPrice(Product $product): void
    {
        $finalPrice = $this->calculateFinalPrice($product);
        $product->update(['final_price' => $finalPrice]);
    }

    /**
     * Обновить итоговые цены для всех товаров
     */
    public function updateAllProductsFinalPrices(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            $this->updateProductFinalPrice($product);
        }
    }
}
