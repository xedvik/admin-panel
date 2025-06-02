<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'price',
        'compare_price',
        'stock_quantity',
        'track_quantity',
        'continue_selling_when_out_of_stock',
        'weight',
        'weight_unit',
        'images',
        'meta_title',
        'meta_description',
        'category_id',
        'is_active',
        'is_featured',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'weight' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'track_quantity' => 'boolean',
        'continue_selling_when_out_of_stock' => 'boolean',
        'images' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * Получить категорию товара
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Получить позиции заказов с данным товаром
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Только активные товары
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Только опубликованные товары
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Только рекомендуемые товары
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Товары в наличии
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('track_quantity', false)
                  ->orWhere('stock_quantity', '>', 0)
                  ->orWhere('continue_selling_when_out_of_stock', true);
        });
    }

    /**
     * Товары по категории
     */
    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Проверить есть ли товар в наличии
     */
    public function isInStock(): bool
    {
        if (!$this->track_quantity) {
            return true;
        }

        if ($this->stock_quantity > 0) {
            return true;
        }

        return $this->continue_selling_when_out_of_stock;
    }

    /**
     * Получить цену со скидкой (если есть compare_price)
     */
    public function getDiscountPercentAttribute(): ?int
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }

        return round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    /**
     * Получить основное изображение
     */
    public function getMainImageAttribute(): ?string
    {
        if (empty($this->images)) {
            return null;
        }

        return $this->images[0] ?? null;
    }

    /**
     * Получить статус наличия
     */
    public function getStockStatusAttribute(): string
    {
        if (!$this->track_quantity) {
            return 'В наличии';
        }

        if ($this->stock_quantity > 10) {
            return 'В наличии';
        } elseif ($this->stock_quantity > 0) {
            return 'Мало в наличии';
        } elseif ($this->continue_selling_when_out_of_stock) {
            return 'Под заказ';
        }

        return 'Нет в наличии';
    }
}
