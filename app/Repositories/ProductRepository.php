<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductAttributeValueRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\ProductPriceService;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    protected ProductAttributeValueRepositoryInterface $attributeValueRepository;

    public function __construct(Product $model, ProductAttributeValueRepositoryInterface $attributeValueRepository)
    {
        parent::__construct($model);
        $this->attributeValueRepository = $attributeValueRepository;
    }

    /**
     * Получить активные товары
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }


    /**
     * Получить популярные товары
     */
    public function getFeatured(): Collection
    {
        return $this->model->where('is_featured', true)->get();
    }

    /**
     * Получить товары в наличии
     */
    public function getInStock(): Collection
    {
        return $this->model->where(function ($query) {
            $query->where('track_quantity', false)
                  ->orWhere('stock_quantity', '>', 0)
                  ->orWhere('continue_selling_when_out_of_stock', true);
        })->get();
    }



    /**
     * Найти товар по SKU
     */
    public function findBySku(string $sku): ?Product
    {
        return $this->model->where('sku', $sku)->first();
    }

    /**
     * Найти товар по slug
     */
    public function findBySlug(string $slug): ?Product
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Поиск товаров по названию
     */
    public function searchByName(string $search): Collection
    {
        return $this->model->where('name', 'like', "%{$search}%")->get();
    }

    /**
     * Проверить, есть ли товар в наличии
     */
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

    /**
     * Обновить остатки товара
     */
    public function updateStock(int $productId, int $quantity): Product
    {
        $product = $this->findOrFail($productId);
        $product->update(['stock_quantity' => $quantity]);

        return $product->fresh();
    }

    /**
     * Уменьшить остатки товара
     */
    public function decrementStock(int $productId, int $quantity): Product
    {
        $product = $this->findOrFail($productId);

        if ($product->track_quantity) {
            $newQuantity = max(0, $product->stock_quantity - $quantity);
            $product->update(['stock_quantity' => $newQuantity]);
        }

        return $product->fresh();
    }

    /**
     * Увеличить остатки товара
     */
    public function incrementStock(int $productId, int $quantity): Product
    {
        $product = $this->findOrFail($productId);

        if ($product->track_quantity) {
            $newQuantity = $product->stock_quantity + $quantity;
            $product->update(['stock_quantity' => $newQuantity]);
        }

        return $product->fresh();
    }

    /**
     * Получить товары с низким остатком
     */
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return $this->model->where('track_quantity', true)
                          ->where('stock_quantity', '<=', $threshold)
                          ->where('stock_quantity', '>', 0)
                          ->get();
    }

    /**
     * Получить товары без остатков
     */
    public function getOutOfStockProducts(): Collection
    {
        return $this->model->where('track_quantity', true)
                          ->where('stock_quantity', '<=', 0)
                          ->where('continue_selling_when_out_of_stock', false)
                          ->get();
    }

    /**
     * Получить популярные товары
     */
    public function getPopularProducts(int $limit = 10): Collection
    {
        return $this->model->withCount('orderItems')
                          ->orderBy('order_items_count', 'desc')
                          ->limit($limit)
                          ->get();
    }


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

    /**
     * Получить товары с атрибутами
     */
    public function getWithAttributes(): Collection
    {
        return $this->model->with('attributeValues.attribute')->get();
    }

    /**
     * Получить товары по значению атрибута
     */
    public function getByAttributeValue(int $attributeId, string $value): Collection
    {
        return $this->attributeValueRepository->getProductsByAttributeValue($attributeId, $value);
    }

    /**
     * Получить уникальные значения атрибута для товаров в категории
     */
    public function getAttributeValuesForCategory(int $categoryId, int $attributeId): Collection
    {
        return $this->model->where('category_id', $categoryId)
            ->whereHas('attributeValues', function ($query) use ($attributeId) {
                $query->where('attribute_id', $attributeId);
            })
            ->with(['attributeValues' => function ($query) use ($attributeId) {
                $query->where('attribute_id', $attributeId);
            }])
            ->get()
            ->pluck('attributeValues')
            ->flatten()
            ->unique('value');
    }

    /**
     * Фильтровать товары по атрибутам
     */
    public function filterByAttributes(array $attributeFilters): Collection
    {
        $query = $this->model->query();

        foreach ($attributeFilters as $attributeId => $values) {
            if (!is_array($values)) {
                $values = [$values];
            }

            $query->whereHas('attributeValues', function ($subQuery) use ($attributeId, $values) {
                $subQuery->where('attribute_id', $attributeId)
                         ->whereIn('value', $values);
            });
        }

        return $query->get();
    }

    /**
     * Синхронизировать атрибуты товара
     */
    public function syncAttributes(int $productId, array $attributes): bool
    {
        try {
            // Удаляем старые атрибуты
            $this->attributeValueRepository->deleteByProduct($productId);

            // Добавляем новые
            foreach ($attributes as $attributeId => $value) {
                if (!empty($value)) {
                    $this->attributeValueRepository->updateOrCreateForProduct($productId, $attributeId, $value);
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Удалить все атрибуты товара
     */
    public function clearAttributes(int $productId): bool
    {
        return $this->attributeValueRepository->deleteByProduct($productId);
    }

    /**
     * Обновить итоговую цену товара
     */
    public function updateFinalPrice(Product $product): void
    {
        $product->update(['final_price' => $product->final_price]);
    }
}
