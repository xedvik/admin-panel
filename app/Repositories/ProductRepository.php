<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function getPublished(): Collection
    {
        return $this->model->where('is_active', true)
                          ->whereNotNull('published_at')
                          ->where('published_at', '<=', now())
                          ->get();
    }

    public function getFeatured(): Collection
    {
        return $this->model->where('is_featured', true)->get();
    }

    public function getInStock(): Collection
    {
        return $this->model->where(function ($query) {
            $query->where('track_quantity', false)
                  ->orWhere('stock_quantity', '>', 0)
                  ->orWhere('continue_selling_when_out_of_stock', true);
        })->get();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->model->where('category_id', $categoryId)->get();
    }

    public function getByCategoryPaginated(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('category_id', $categoryId)->paginate($perPage);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->where('sku', $sku)->first();
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function searchByName(string $search): Collection
    {
        return $this->model->where('name', 'like', "%{$search}%")->get();
    }

    public function isInStock(int $productId): bool
    {
        $product = $this->find($productId);

        if (!$product) {
            return false;
        }

        if (!$product->track_quantity) {
            return true;
        }

        if ($product->stock_quantity > 0) {
            return true;
        }

        return $product->continue_selling_when_out_of_stock;
    }

    public function updateStock(int $productId, int $quantity): Product
    {
        $product = $this->findOrFail($productId);
        $product->update(['stock_quantity' => $quantity]);

        return $product->fresh();
    }

    public function decrementStock(int $productId, int $quantity): Product
    {
        $product = $this->findOrFail($productId);

        if ($product->track_quantity) {
            $newQuantity = max(0, $product->stock_quantity - $quantity);
            $product->update(['stock_quantity' => $newQuantity]);
        }

        return $product->fresh();
    }

    public function incrementStock(int $productId, int $quantity): Product
    {
        $product = $this->findOrFail($productId);

        if ($product->track_quantity) {
            $newQuantity = $product->stock_quantity + $quantity;
            $product->update(['stock_quantity' => $newQuantity]);
        }

        return $product->fresh();
    }

    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return $this->model->where('track_quantity', true)
                          ->where('stock_quantity', '<=', $threshold)
                          ->where('stock_quantity', '>', 0)
                          ->get();
    }

    public function getOutOfStockProducts(): Collection
    {
        return $this->model->where('track_quantity', true)
                          ->where('stock_quantity', '<=', 0)
                          ->where('continue_selling_when_out_of_stock', false)
                          ->get();
    }

    /**
     * Получить товары с категориями
     */
    // public function getWithCategories(): Collection
    // {
    //     return $this->model->with('category')->get();
    // }

    /**
     * Получить популярные товары (по количеству заказов)
     */
    public function getPopularProducts(int $limit = 10): Collection
    {
        return $this->model->withCount('orderItems')
                          ->orderBy('order_items_count', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Получить товары со скидкой
     */
    // public function getDiscountedProducts(): Collection
    // {
    //     return $this->model->whereNotNull('compare_price')
    //                       ->whereColumn('compare_price', '>', 'price')
    //                       ->get();
    // }

    /**
     * Поиск товаров по нескольким полям
     */
    // public function searchProducts(string $search): Collection
    // {
    //     return $this->model->where(function ($query) use ($search) {
    //         $query->where('name', 'like', "%{$search}%")
    //               ->orWhere('description', 'like', "%{$search}%")
    //               ->orWhere('sku', 'like', "%{$search}%");
    //     })->get();
    // }

    /**
     * Получить query builder для товаров
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->query();
    }

    /**
     * Вычислить процент скидки для товара
     */
    public function calculateDiscountPercent(Product $product): int
    {
        if (!$product->compare_price || $product->compare_price <= $product->price) {
            return 0;
        }

        return (int) round((($product->compare_price - $product->price) / $product->compare_price) * 100);
    }

    /**
     * Получить главное изображение товара
     */
    public function getMainImage(Product $product): ?string
    {
        if (!$product->images || !is_array($product->images) || empty($product->images)) {
            return null;
        }

        return $product->images[0] ?? null;
    }

    /**
     * Получить статус остатков товара
     */
    public function getStockStatus(Product $product): string
    {
        if (!$product->track_quantity) {
            return 'Под заказ';
        }

        if ($product->stock_quantity <= 0) {
            return $product->continue_selling_when_out_of_stock ? 'Под заказ' : 'Нет в наличии';
        }

        if ($product->stock_quantity <= 5) {
            return 'Мало в наличии';
        }

        return 'В наличии';
    }

    /**
     * Проверить доступность товара в наличии
     */
    public function checkInStock(Product $product): bool
    {
        if (!$product->track_quantity) {
            return true;
        }

        if ($product->stock_quantity > 0) {
            return true;
        }

        return $product->continue_selling_when_out_of_stock;
    }
}
