<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'accepts_marketing',
        'email_verified_at',
        'is_active',
        'status',
        'client_status_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'accepts_marketing' => 'boolean',
        'is_active' => 'boolean',
        'email_verified_at' => 'timestamp',
    ];

    /**
     * Получить заказы клиента
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }

    /**
     * Получить адреса клиента
     */
    public function clientAddresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ClientStatus::class, 'client_status_id');
    }
}
