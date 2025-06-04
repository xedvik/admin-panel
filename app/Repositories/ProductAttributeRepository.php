<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductAttributeRepositoryInterface;
use App\Models\ProductAttribute;
use Illuminate\Support\Collection;

class ProductAttributeRepository extends BaseRepository implements ProductAttributeRepositoryInterface
{
    public function __construct(ProductAttribute $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getSelectOptions(int $attributeId): array
    {
        $attribute = $this->find($attributeId);

        if (!$attribute || $attribute->type !== 'select' || !$attribute->options) {
            return [];
        }

        return array_combine($attribute->options, $attribute->options);
    }

    public function findBySlug(string $slug): ?ProductAttribute
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function getWithValuesCount(): Collection
    {
        return $this->model->withCount('values')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
