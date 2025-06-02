<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'product_sku' => $this->product_sku,
            'quantity' => (int) $this->quantity,
            'price' => (int) $this->price,
            'total_price' => (int) $this->total_price,
            'product_variant' => $this->product_variant,
            'notes' => $this->notes,

            // Форматированные цены
            'price_formatted' => '₽' . number_format($this->price, 0, ',', ' '),
            'total_price_formatted' => '₽' . number_format($this->total_price, 0, ',', ' '),

            // Связи
            'product' => $this->whenLoaded('product', function () {
                return new ProductResource($this->product);
            }),

            // Временные метки
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
