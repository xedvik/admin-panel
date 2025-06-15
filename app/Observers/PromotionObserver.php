<?php

namespace App\Observers;

use App\Models\Promotion;
use App\Services\ProductPriceService;

class PromotionObserver
{
    public function __construct(
        private ProductPriceService $priceService
    ) {}

    /**
     * Обработка события после обновления акции
     */
    public function updated(Promotion $promotion): void
    {
        // Обновляем цены всех связанных товаров
        foreach ($promotion->products as $product) {
            $this->priceService->updateProductFinalPrice($product);
        }
    }

    /**
     * Обработка события после удаления акции
     */
    public function deleted(Promotion $promotion): void
    {
        // Обновляем цены всех связанных товаров
        foreach ($promotion->products as $product) {
            $this->priceService->updateProductFinalPrice($product);
        }
    }
}
