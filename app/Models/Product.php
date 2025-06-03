<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'price' => 'integer',
        'compare_price' => 'integer',
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


}
