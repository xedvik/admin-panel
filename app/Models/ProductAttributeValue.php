<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_id',
        'value',
    ];

    /**
     * Товар
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Атрибут
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    /**
     * Получить отформатированное значение в зависимости от типа атрибута
     */
    public function getFormattedValueAttribute(): string
    {
        if (!$this->attribute) {
            return $this->value;
        }

        return match ($this->attribute->type) {
            'boolean' => $this->value ? 'Да' : 'Нет',
            'number' => number_format((float) $this->value, 2, ',', ' '),
            'date' => $this->value ? \Carbon\Carbon::parse($this->value)->format('d.m.Y') : '',
            default => $this->value,
        };
    }

    /**
     * Получить значение с правильным типом в зависимости от атрибута
     */
    public function getCastedValueAttribute()
    {
        if (!$this->attribute) {
            return $this->value;
        }

        return match ($this->attribute->type) {
            'boolean' => (bool) $this->value,
            'number' => (float) $this->value,
            'date' => $this->value ? \Carbon\Carbon::parse($this->value) : null,
            default => $this->value,
        };
    }
}
