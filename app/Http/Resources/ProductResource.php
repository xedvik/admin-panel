<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'description' => $this->description,

            // Цены и скидки
            'price' => (int) $this->price,
            'compare_price' => $this->compare_price ? (int) $this->compare_price : null,
            'discount_percentage' => $this->discount_percentage,
            'price_formatted' => '₽' . number_format($this->price, 0, ',', ' '),
            'has_discount' => $this->compare_price && $this->compare_price > $this->price,

            // Остатки
            'stock_quantity' => (int) $this->stock_quantity,
            'track_quantity' => $this->track_quantity,
            'continue_selling_when_out_of_stock' => $this->continue_selling_when_out_of_stock,
            'stock_status' => $this->stock_status,
            'in_stock' => $this->in_stock,

            // Характеристики
            'weight' => $this->weight ? (float) $this->weight : null,
            'weight_unit' => $this->weight_unit,
            'dimensions' => $this->dimensions,

            // Изображения
            'images' => $this->images ? collect($this->images)->map(function ($image) {
                return asset('storage/' . $image);
            })->toArray() : [],
            'main_image' => $this->main_image ? asset('storage/' . $this->main_image) : null,

            // Статус
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at?->toISOString(),

            // Связи
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () {
                return new CategoryResource($this->category);
            }),

            // SEO
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,

            // Временные метки
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
