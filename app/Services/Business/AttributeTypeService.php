<?php

namespace App\Services\Business;

use App\Models\ProductAttribute;
use Carbon\Carbon;

class AttributeTypeService
{
    /**
     * Получить все доступные типы атрибутов с переводами
     */
    public function getTypeOptions(): array
    {
        return [
            'text' => 'Текст',
            'number' => 'Число',
            'select' => 'Выбор из списка',
            'boolean' => 'Да/Нет',
            'date' => 'Дата',
        ];
    }

    /**
     * Получить перевод типа атрибута
     */
    public function getTypeLabel(?string $type): string
    {
        if (!$type) {
            return '';
        }

        return $this->getTypeOptions()[$type] ?? $type;
    }

    /**
     * Получить цвет badge для типа атрибута
     */
    public function getTypeBadgeColor(?string $type): string
    {
        if (!$type) {
            return 'gray';
        }

        return match ($type) {
            'text' => 'gray',
            'number' => 'blue',
            'select' => 'green',
            'boolean' => 'orange',
            'date' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Форматировать значение атрибута в зависимости от типа
     */
    public function formatValue(ProductAttribute $attribute, $value): string
    {
        if (empty($value)) {
            return '';
        }

        return match ($attribute->type) {
            'boolean' => $value ? 'Да' : 'Нет',
            'number' => number_format((float) $value, 2, ',', ' '),
            'date' => $value ? Carbon::parse($value)->format('d.m.Y') : '',
            default => (string) $value,
        };
    }

    /**
     * Получить правильно приведенное значение атрибута
     */
    public function getCastedValue(ProductAttribute $attribute, $value)
    {
        if (empty($value)) {
            return null;
        }

        return match ($attribute->type) {
            'boolean' => (bool) $value,
            'number' => (float) $value,
            'date' => $value ? Carbon::parse($value) : null,
            default => $value,
        };
    }

    /**
     * Проверить, нужно ли показывать поле options для типа
     */
    public function shouldShowOptionsField(?string $type): bool
    {
        return $type === 'select';
    }

    /**
     * Валидировать значение атрибута
     */
    public function validateValue(ProductAttribute $attribute, $value): bool
    {
        if ($attribute->is_required && empty($value)) {
            return false;
        }

        return match ($attribute->type) {
            'number' => is_numeric($value),
            'boolean' => in_array($value, [true, false, 1, 0, '1', '0']),
            'date' => $this->isValidDate($value),
            'select' => $this->isValidSelectOption($attribute, $value),
            default => true,
        };
    }

    /**
     * Проверить, является ли значение валидной датой
     */
    private function isValidDate($value): bool
    {
        try {
            Carbon::parse($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Проверить, является ли значение валидной опцией для select
     */
    private function isValidSelectOption(ProductAttribute $attribute, $value): bool
    {
        if (!$attribute->options || !is_array($attribute->options)) {
            return false;
        }

        return in_array($value, $attribute->options);
    }
}
