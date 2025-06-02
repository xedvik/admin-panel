<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'addresses',
        'accepts_marketing',
        'email_verified_at',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'accepts_marketing' => 'boolean',
        'is_active' => 'boolean',
        'email_verified_at' => 'timestamp',
        'addresses' => 'array',
    ];

    /**
     * Получить заказы клиента
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }

    /**
     * Только активные клиенты
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Клиенты с подтвержденным email
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Клиенты, согласившиеся на маркетинг
     */
    public function scopeAcceptsMarketing(Builder $query): Builder
    {
        return $query->where('accepts_marketing', true);
    }

    /**
     * Поиск по имени или email
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Получить полное имя клиента
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Получить инициалы клиента
     */
    public function getInitialsAttribute(): string
    {
        $firstInitial = $this->first_name ? mb_substr($this->first_name, 0, 1) : '';
        $lastInitial = $this->last_name ? mb_substr($this->last_name, 0, 1) : '';

        return mb_strtoupper($firstInitial . $lastInitial);
    }

    /**
     * Проверить подтвержден ли email
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Получить основной адрес доставки
     */
    public function getPrimaryAddressAttribute(): ?array
    {
        if (empty($this->addresses)) {
            return null;
        }

        // Ищем адрес по умолчанию
        foreach ($this->addresses as $address) {
            if (isset($address['is_default']) && $address['is_default']) {
                return $address;
            }
        }

        // Если нет адреса по умолчанию, возвращаем первый
        return $this->addresses[0] ?? null;
    }

    /**
     * Получить общую сумму заказов клиента
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->orders()
                   ->where('payment_status', 'paid')
                   ->sum('total_amount');
    }

    /**
     * Получить количество заказов клиента
     */
    public function getTotalOrdersAttribute(): int
    {
        return $this->orders()->count();
    }

    /**
     * Получить статус клиента (новый, постоянный, VIP)
     */
    public function getCustomerStatusAttribute(): string
    {
        $totalSpent = $this->total_spent;
        $totalOrders = $this->total_orders;

        if ($totalOrders === 0) {
            return 'Новый';
        } elseif ($totalSpent >= 100000 || $totalOrders >= 10) {
            return 'VIP';
        } elseif ($totalOrders >= 3) {
            return 'Постоянный';
        }

        return 'Обычный';
    }
}
