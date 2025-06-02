<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'description' => $this->description,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,

            // SEO данные
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,

            // Иерархия
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', function () {
                return new CategoryResource($this->parent);
            }),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'path' => $this->when($this->relationLoaded('parent'), function () {
                return $this->full_path;
            }),

            // Статистика
            'products_count' => $this->whenCounted('products'),

            // Временные метки
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
