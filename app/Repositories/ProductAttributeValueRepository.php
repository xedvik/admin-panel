<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductAttributeValueRepositoryInterface;
use App\Models\ProductAttributeValue;
use App\Services\Business\AttributeTypeService;
use Illuminate\Support\Collection;

class ProductAttributeValueRepository extends BaseRepository implements ProductAttributeValueRepositoryInterface
{
    public function __construct(
        ProductAttributeValue $model,
        private AttributeTypeService $attributeTypeService
    ) {
        parent::__construct($model);
    }

    public function getByProduct(int $productId): Collection
    {
        return $this->model->where('product_id', $productId)
            ->with('attribute')
            ->get();
    }

    public function getByAttribute(int $attributeId): Collection
    {
        return $this->model->where('attribute_id', $attributeId)
            ->with('product')
            ->get();
    }

    public function updateOrCreateForProduct(int $productId, int $attributeId, string $value): ProductAttributeValue
    {
        return $this->model->updateOrCreate(
            [
                'product_id' => $productId,
                'attribute_id' => $attributeId,
            ],
            [
                'value' => $value,
            ]
        );
    }

    public function deleteByProduct(int $productId): bool
    {
        return $this->model->where('product_id', $productId)->delete();
    }

    public function getProductsByAttributeValue(int $attributeId, string $value): Collection
    {
        return $this->model->where('attribute_id', $attributeId)
            ->where('value', $value)
            ->with('product')
            ->get()
            ->pluck('product');
    }

    /**
     * Получить отформатированное значение в зависимости от типа атрибута
     */
    public function getFormattedValue(int $valueId): string
    {
        $attributeValue = $this->findWithAttribute($valueId);

        if (!$attributeValue || !$attributeValue->attribute) {
            return $attributeValue->value ?? '';
        }

        return $this->attributeTypeService->formatValue($attributeValue->attribute, $attributeValue->value);
    }

    /**
     * Получить значение с правильным типом в зависимости от атрибута
     */
    public function getCastedValue(int $valueId)
    {
        $attributeValue = $this->findWithAttribute($valueId);

        if (!$attributeValue || !$attributeValue->attribute) {
            return $attributeValue->value ?? null;
        }

        return $this->attributeTypeService->getCastedValue($attributeValue->attribute, $attributeValue->value);
    }

    /**
     * Найти значение атрибута с загруженным атрибутом
     */
    private function findWithAttribute(int $valueId): ?ProductAttributeValue
    {
        return $this->model->with('attribute')->find($valueId);
    }
}
