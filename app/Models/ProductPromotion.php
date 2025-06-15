<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Services\ProductPriceService;

class ProductPromotion extends Pivot
{
    protected static function booted()
    {
        static::saved(function (ProductPromotion $pivot) {
            $product = \App\Models\Product::find($pivot->product_id);
            if ($product) {
                app(ProductPriceService::class)->updateProductFinalPrice($product);
            }
        });

        static::deleted(function (ProductPromotion $pivot) {
            $product = \App\Models\Product::find($pivot->product_id);
            if ($product) {
                app(ProductPriceService::class)->updateProductFinalPrice($product);
            }
        });
    }
}
