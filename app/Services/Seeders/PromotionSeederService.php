<?php

namespace App\Services\Seeders;

use App\Contracts\Repositories\PromotionRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;

class PromotionSeederService
{
    public function __construct(
        private PromotionRepositoryInterface $promotionRepository,
        private ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Создать тестовые акции
     */
    public function createPromotions(): void
    {
        $promotions = $this->getPromotionsData();
        $products = $this->productRepository->getActive();

        // Создаем акции
        foreach ($promotions as $promotionData) {
            $this->promotionRepository->create($promotionData);
        }

        // Получаем все созданные акции
        $createdPromotions = $this->promotionRepository->getAll();

        // Проходим по всем товарам
        foreach ($products as $product) {
            // С вероятностью 70% привязываем к товару случайную акцию
            if (fake()->boolean(70)) {
                $randomPromotion = $createdPromotions->random();
                $product->promotions()->attach($randomPromotion->id);
            }
        }
    }

    /**
     * Получить данные тестовых акций
     */
    private function getPromotionsData(): array
    {
        for ($i = 0; $i < 20; $i++) {
            $promotions[] = [
                'name' => fake()->sentence(3),
                'description' => fake()->paragraph(3),
                'start_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
                'end_date' => fake()->dateTimeBetween('+1 month', '+2 months'),
                'discount_type' => fake()->randomElement(['percentage', 'fixed']),
                'discount_value' => fake()->numberBetween(10, 50),
                'is_active' => true,
            ];
        }
        return $promotions;
    }
}
