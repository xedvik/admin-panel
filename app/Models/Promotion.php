<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'discount_type',
        'discount_value',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'discount_value' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Получить товары, на которые распространяется акция
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->using(\App\Models\ProductPromotion::class)
            ->withTimestamps();
    }
}
