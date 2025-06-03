<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Проверить подтвержден ли email
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Получить полное имя клиента
     */
    public function getFullNameAttribute(): string
    {
        return app(\App\Contracts\Repositories\ClientRepositoryInterface::class)
            ->getClientFullName($this->id);
    }
}
