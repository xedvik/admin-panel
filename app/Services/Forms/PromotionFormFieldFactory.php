<?php

namespace App\Services\Forms;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class PromotionFormFieldFactory
{
    /**
     * Создать основную структуру формы
     */
    public function createMainLayout(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Section::make('Основная информация')
                        ->schema([
                            ...$this->createBaseFields(),
                        ])
                        ->columnSpan(1),

                    Section::make('Настройки')
                        ->schema([
                            $this->createActiveField(),
                            $this->createProductsField(),
                        ])
                        ->columnSpan(1),
                ]),
        ];
    }

    /**
     * Создать базовые поля формы
     */
    private function createBaseFields(): array
    {
        return [
            TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255),

            TextInput::make('description')
                ->label('Описание')
                ->maxLength(1000),

            DatePicker::make('start_date')
                ->label('Дата начала')
                ->required(),

            DatePicker::make('end_date')
                ->label('Дата окончания')
                ->required(),

            Select::make('discount_type')
                ->label('Тип скидки')
                ->options([
                    'percentage' => 'Процентная скидка',
                    'fixed' => 'Фиксированная скидка',
                ])
                ->required()
                ->reactive(),

            TextInput::make('discount_value')
                ->label('Значение скидки')
                ->numeric()
                ->minValue(0)
                ->required()
                ->suffix(fn ($get) => $get('discount_type') === 'percentage' ? '%' : '₽')
                ->reactive(),
        ];
    }

    /**
     * Создать поле активности
     */
    private function createActiveField(): Forms\Components\Toggle
    {
        return Toggle::make('is_active')
            ->label('Активна')
            ->default(true);
    }

    /**
     * Создать поле выбора товаров
     */
    private function createProductsField(): Forms\Components\Select
    {
        return Select::make('products')
            ->label('Товары')
            ->multiple()
            ->relationship('products', 'name')
            ->preload()
            ->searchable();
    }
}
