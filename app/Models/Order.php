<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['order_number', 'client_id', 'status', 'subtotal', 'tax_amount', 'shipping_amount', 'discount_amount',
     'total_amount', 'currency', 'payment_status', 'payment_method', 'billing_address', 'shipping_address', 'notes', 'shipped_at', 'delivered_at'];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
