<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductAttributeValueRepositoryInterface;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function fillForm(): void
    {
        $data = $this->record->attributesToArray();

        // Заполняем атрибуты
        $attributeValueRepository = app(ProductAttributeValueRepositoryInterface::class);
        $attributeValues = $attributeValueRepository->getByProduct($this->record->getKey());

        foreach ($attributeValues as $attributeValue) {
            $data["attribute_{$attributeValue->attribute_id}"] = $attributeValue->value;
        }

        $this->form->fill($data);
    }

    protected function beforeSave(): void
    {
        $this->handleAttributes();
    }

    protected function handleAttributes(): void
    {
        $productRepository = app(ProductRepositoryInterface::class);
        $attributes = [];

        foreach ($this->data as $key => $value) {
            if (str_starts_with($key, 'attribute_')) {
                $attributeId = (int) str_replace('attribute_', '', $key);
                if (!empty($value)) {
                    $attributes[$attributeId] = (string) $value;
                }
            }
        }

        $productRepository->syncAttributes($this->record->getKey(), $attributes);
    }
}
