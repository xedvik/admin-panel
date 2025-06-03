<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'product_price',
        'total_price',
        'product_variant',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'product_price' => 'integer',
        'total_price' => 'integer',
        'product_variant' => 'array',
    ];

    /**
     * Получить заказ позиции
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Получить товар позиции
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


}
