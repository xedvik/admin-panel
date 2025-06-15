<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Services\ProductPriceService;

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
        'final_price',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'integer',
        'weight' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'track_quantity' => 'boolean',
        'continue_selling_when_out_of_stock' => 'boolean',
        'images' => 'array',
        'published_at' => 'datetime',
        'final_price' => 'integer',
    ];

    /**
     * Мутатор для поля final_price
     * Если final_price не установлен, используем оригинальную цену
     */
    public function setFinalPriceAttribute($value)
    {
        $this->attributes['final_price'] = $value ?? $this->price;
    }

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
     * Получить значения атрибутов товара
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    /**
     * Получить акции, связанные с товаром
     */
    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class)
            ->using(\App\Models\ProductPromotion::class)
            ->withTimestamps();
    }

    protected static function booted()
    {
        static::creating(function ($product) {
            if (is_null($product->final_price)) {
                $product->final_price = $product->price;
            }
        });
    }

}
