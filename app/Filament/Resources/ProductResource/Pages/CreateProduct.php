<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Contracts\Repositories\ProductRepositoryInterface;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $this->handleAttributes();
    }

    protected function handleAttributes(): void
    {
        $productRepository = app(ProductRepositoryInterface::class);
        $attributes = [];

        foreach ($this->data as $key => $value) {
            if (str_starts_with($key, 'attribute_') && !empty($value)) {
                $attributeId = (int) str_replace('attribute_', '', $key);
                $attributes[$attributeId] = (string) $value;
            }
        }

        if (!empty($attributes)) {
            $productRepository->syncAttributes($this->record->getKey(), $attributes);
        }
    }
}
