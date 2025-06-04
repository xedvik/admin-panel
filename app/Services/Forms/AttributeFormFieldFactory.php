<?php

namespace App\Services\Forms;

use App\Models\ProductAttribute;
use App\Services\Business\AttributeTypeService;
use Filament\Forms;

class AttributeFormFieldFactory
{
    public function __construct(
        private AttributeTypeService $attributeTypeService
    ) {}

    /**
     * Создать поле формы для атрибута
     */
    public function createFieldForAttribute(ProductAttribute $attribute): Forms\Components\Component
    {
        return match ($attribute->type) {
            'text' => $this->createTextField($attribute),
            'number' => $this->createNumberField($attribute),
            'select' => $this->createSelectField($attribute),
            'boolean' => $this->createBooleanField($attribute),
            'date' => $this->createDateField($attribute),
            default => $this->createTextField($attribute),
        };
    }

    /**
     * Создать текстовое поле
     */
    private function createTextField(ProductAttribute $attribute): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make("attribute_{$attribute->id}")
            ->label($attribute->name)
            ->maxLength(255)
            ->helperText($attribute->description)
            ->required($attribute->is_required)
            ->dehydrated(false);
    }

    /**
     * Создать числовое поле
     */
    private function createNumberField(ProductAttribute $attribute): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make("attribute_{$attribute->id}")
            ->label($attribute->name)
            ->numeric()
            ->helperText($attribute->description)
            ->required($attribute->is_required)
            ->dehydrated(false);
    }

    /**
     * Создать поле выбора
     */
    private function createSelectField(ProductAttribute $attribute): ?Forms\Components\Select
    {
        if (empty($attribute->options)) {
            return null;
        }

        $options = array_combine($attribute->options, $attribute->options);

        return Forms\Components\Select::make("attribute_{$attribute->id}")
            ->label($attribute->name)
            ->options($options)
            ->searchable()
            ->helperText($attribute->description)
            ->required($attribute->is_required)
            ->dehydrated(false);
    }

    /**
     * Создать поле boolean
     */
    private function createBooleanField(ProductAttribute $attribute): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make("attribute_{$attribute->id}")
            ->label($attribute->name)
            ->helperText($attribute->description)
            ->required($attribute->is_required)
            ->dehydrated(false);
    }

    /**
     * Создать поле даты
     */
    private function createDateField(ProductAttribute $attribute): Forms\Components\DatePicker
    {
        return Forms\Components\DatePicker::make("attribute_{$attribute->id}")
            ->label($attribute->name)
            ->helperText($attribute->description)
            ->required($attribute->is_required)
            ->dehydrated(false);
    }

    /**
     * Создать все поля для активных атрибутов
     */
    public function createFieldsForActiveAttributes(): array
    {
        $attributeRepository = app(\App\Contracts\Repositories\ProductAttributeRepositoryInterface::class);
        $attributes = $attributeRepository->getActiveOrdered();

        if ($attributes->isEmpty()) {
            return [
                Forms\Components\Placeholder::make('no_attributes')
                    ->label('Атрибуты не настроены')
                    ->content('Сначала создайте атрибуты товаров в разделе "Атрибуты товаров"'),
            ];
        }

        $fields = [];

        foreach ($attributes as $attribute) {
            $field = $this->createFieldForAttribute($attribute);
            if ($field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Создать записи для отображения атрибутов в infolist
     */
    public function createInfolistEntriesForProduct($productId): array
    {
        $attributeValueRepository = app(\App\Contracts\Repositories\ProductAttributeValueRepositoryInterface::class);
        $attributeValues = $attributeValueRepository->getByProduct($productId);

        if ($attributeValues->isEmpty()) {
            return [];
        }

        $entries = [];

        foreach ($attributeValues as $attributeValue) {
            if (!empty($attributeValue->value)) {
                $formattedValue = $this->attributeTypeService->formatValue(
                    $attributeValue->attribute,
                    $attributeValue->value
                );

                $entries[] = \Filament\Infolists\Components\TextEntry::make("attribute_{$attributeValue->attribute_id}")
                    ->label($attributeValue->attribute->name)
                    ->state($formattedValue)
                    ->helperText($attributeValue->attribute->description);
            }
        }

        return $entries;
    }
}
