<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

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
     * Заказы по статусу
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Заказы по статусу оплаты
     */
    public function scopePaymentStatus(Builder $query, string $paymentStatus): Builder
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Заказы в ожидании
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Заказы в обработке
     */
    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    /**
     * Отправленные заказы
     */
    public function scopeShipped(Builder $query): Builder
    {
        return $query->where('status', 'shipped');
    }

    /**
     * Доставленные заказы
     */
    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Отмененные заказы
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Оплаченные заказы
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Поиск по номеру заказа
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('order_number', 'like', "%{$search}%");
    }

    /**
     * Проверить можно ли отменить заказ
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Проверить отправлен ли заказ
     */
    public function isShipped(): bool
    {
        return $this->status === 'shipped' && !is_null($this->shipped_at);
    }

    /**
     * Проверить доставлен ли заказ
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered' && !is_null($this->delivered_at);
    }

    /**
     * Проверить оплачен ли заказ
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Получить количество товаров в заказе
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->orderItems()->sum('quantity');
    }

    /**
     * Получить русское название статуса
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'В ожидании',
            'processing' => 'В обработке',
            'shipped' => 'Отправлен',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменен',
            default => 'Неизвестно',
        };
    }

    /**
     * Получить русское название статуса оплаты
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'Ожидает оплаты',
            'paid' => 'Оплачен',
            'failed' => 'Ошибка оплаты',
            'refunded' => 'Возврат',
            default => 'Неизвестно',
        };
    }

    /**
     * Получить CSS класс для статуса
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Получить полный адрес доставки как строку
     */
    public function getShippingAddressStringAttribute(): string
    {
        if (empty($this->shipping_address)) {
            return '';
        }

        $address = $this->shipping_address;
        $parts = [];

        if (!empty($address['street'])) $parts[] = $address['street'];
        if (!empty($address['city'])) $parts[] = $address['city'];
        if (!empty($address['state'])) $parts[] = $address['state'];
        if (!empty($address['postal_code'])) $parts[] = $address['postal_code'];

        return implode(', ', $parts);
    }
}
