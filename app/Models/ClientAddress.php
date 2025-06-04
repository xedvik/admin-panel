<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAddress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'client_id',
        'type',
        'label',
        'is_default',
        'first_name',
        'last_name',
        'company',
        'street',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Получить клиента этого адреса
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
