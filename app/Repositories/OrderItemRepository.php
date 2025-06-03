<?php

namespace App\Repositories;

use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;

class OrderItemRepository extends BaseRepository implements OrderItemRepositoryInterface
{
    public function __construct(OrderItem $model)
    {
        parent::__construct($model);
    }

    public function getByOrder(int $orderId): Collection
    {
        return $this->model->where('order_id', $orderId)->get();
    }

    public function getByProduct(int $productId): Collection
    {
        return $this->model->where('product_id', $productId)->get();
    }

    public function createWithCalculation(array $data): OrderItem
    {
        // Автоматически рассчитываем общую стоимость
        if (isset($data['quantity']) && isset($data['product_price'])) {
            $data['total_price'] = $data['quantity'] * $data['product_price'];
        }

        return $this->create($data);
    }

    public function updateQuantity(int $orderItemId, int $quantity): OrderItem
    {
        $orderItem = $this->findOrFail($orderItemId);

        $orderItem->update([
            'quantity' => $quantity,
            'total_price' => $quantity * $orderItem->product_price
        ]);

        return $orderItem->fresh();
    }

    public function getTotalQuantityByProduct(int $productId): int
    {
        return $this->model->where('product_id', $productId)->sum('quantity');
    }

    public function getPopularProducts(int $limit = 10): Collection
    {
        return $this->model->selectRaw('product_id, product_name, SUM(quantity) as total_sold')
                          ->groupBy('product_id', 'product_name')
                          ->orderBy('total_sold', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Получить позиции заказа с товарами
     */
    // public function getOrderItemsWithProducts(int $orderId): Collection
    // {
    //     return $this->model->where('order_id', $orderId)->with('product')->get();
    // }

    /**
     * Получить общую сумму продаж по товару
     */
    // public function getTotalSalesByProduct(int $productId): int
    // {
    //     return $this->model->where('product_id', $productId)->sum('total_price');
    // }

    /**
     * Получить статистику продаж за период
     */
    // public function getSalesStatsByDateRange(\DateTime $startDate, \DateTime $endDate): array
    // {
    //     $items = $this->model->whereHas('order', function ($query) use ($startDate, $endDate) {
    //         $query->whereBetween('created_at', [$startDate, $endDate]);
    //     })->get();

    //     return [
    //         'total_items' => $items->sum('quantity'),
    //         'total_amount' => $items->sum('total_price'),
    //         'unique_products' => $items->unique('product_id')->count(),
    //     ];
    // }
}
