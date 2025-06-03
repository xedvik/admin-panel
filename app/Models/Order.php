<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_number',
        'client_id',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'payment_status',
        'payment_method',
        'billing_address',
        'shipping_address',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'subtotal' => 'integer',
        'tax_amount' => 'integer',
        'shipping_amount' => 'integer',
        'discount_amount' => 'integer',
        'total_amount' => 'integer',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'shipped_at' => 'timestamp',
        'delivered_at' => 'timestamp',
    ];

    /**
     * Получить клиента заказа
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Получить позиции заказа
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Получить статус заказа в читаемом виде
     */
    public function getStatusLabelAttribute(): string
    {
        return app(\App\Contracts\Repositories\OrderRepositoryInterface::class)
            ->getOrderStatusLabel($this->id);
    }

    /**
     * Получить статус оплаты в читаемом виде
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return app(\App\Contracts\Repositories\OrderRepositoryInterface::class)
            ->getOrderPaymentStatusLabel($this->id);
    }

    /**
     * Получить цвет для статуса заказа
     */
    public function getStatusColorAttribute(): string
    {
        return app(\App\Contracts\Repositories\OrderRepositoryInterface::class)
            ->getOrderStatusColor($this->id);
    }

    /**
     * Получить количество товаров в заказе
     */
    public function getTotalItemsAttribute(): int
    {
        return app(\App\Contracts\Repositories\OrderRepositoryInterface::class)
            ->getOrderTotalItems($this->id);
    }

}
