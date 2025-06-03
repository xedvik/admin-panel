<?php

namespace App\Contracts\Repositories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;

interface OrderItemRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить позиции заказа
     */
    public function getByOrder(int $orderId): Collection;

    /**
     * Получить позиции с товаром
     */
    public function getByProduct(int $productId): Collection;

    /**
     * Создать позицию заказа с автоматическим расчетом
     */
    public function createWithCalculation(array $data): OrderItem;

    /**
     * Обновить количество и пересчитать сумму
     */
    public function updateQuantity(int $orderItemId, int $quantity): OrderItem;

    /**
     * Получить общее количество проданного товара
     */
    public function getTotalQuantityByProduct(int $productId): int;

    /**
     * Получить популярные товары по продажам
     */
    public function getPopularProducts(int $limit = 10): Collection;
}
