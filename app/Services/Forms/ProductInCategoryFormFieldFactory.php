<?php

namespace App\Services\Forms;

use Filament\Forms;

class ProductInCategoryFormFieldFactory
{
    /**
     * Создать полную схему формы товара в категории
     */
    public function createFullFormSchema(): array
    {
        return [
            $this->createMainInfoSection(),
            $this->createPricesAndStockSection(),
            $this->createStatusAndSettingsSection(),
        ];
    }

    /**
     * Создать секцию основной информации
     */
    public function createMainInfoSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Основная информация')
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        $this->createNameField(),
                        $this->createSlugField(),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        $this->createSkuField(),
                        $this->createCategoryField(),
                    ]),

                $this->createDescriptionField(),
            ]);
    }

    /**
     * Создать секцию цен и остатков
     */
    public function createPricesAndStockSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Цены и остатки')
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        $this->createPriceField(),
                        $this->createComparePriceField(),
                        $this->createCostPriceField(),
                    ]),

                Forms\Components\Grid::make(3)
                    ->schema([
                        $this->createStockQuantityField(),
                        $this->createLowStockThresholdField(),
                        $this->createTrackStockField(),
                    ]),
            ]);
    }

    /**
     * Создать секцию статуса и настроек
     */
    public function createStatusAndSettingsSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Статус и настройки')
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        $this->createIsActiveField(),
                        $this->createIsFeaturedField(),
                        $this->createIsDigitalField(),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        $this->createWeightField(),
                        $this->createDimensionsField(),
                    ]),
            ]);
    }

    /**
     * Создать поле названия
     */
    private function createNameField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('name')
            ->label('Название товара')
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str($state)->slug()));
    }

    /**
     * Создать поле slug
     */
    private function createSlugField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('slug')
            ->label('URL (slug)')
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(255);
    }

    /**
     * Создать поле артикула
     */
    private function createSkuField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('sku')
            ->label('Артикул')
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(100);
    }

    /**
     * Создать поле категории
     */
    private function createCategoryField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('category_id')
            ->label('Категория')
            ->relationship('category', 'name')
            ->searchable()
            ->preload()
            ->required();
    }

    /**
     * Создать поле описания
     */
    private function createDescriptionField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('description')
            ->label('Описание')
            ->rows(3)
            ->maxLength(1000)
            ->columnSpanFull();
    }

    /**
     * Создать поле цены
     */
    private function createPriceField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('price')
            ->label('Цена')
            ->numeric()
            ->required()
            ->prefix('₽')
            ->minValue(0);
    }

    /**
     * Создать поле цены без скидки
     */
    private function createComparePriceField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('compare_price')
            ->label('Цена без скидки')
            ->numeric()
            ->prefix('₽')
            ->minValue(0);
    }

    /**
     * Создать поле себестоимости
     */
    private function createCostPriceField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('cost_price')
            ->label('Себестоимость')
            ->numeric()
            ->prefix('₽')
            ->minValue(0);
    }

    /**
     * Создать поле количества на складе
     */
    private function createStockQuantityField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('stock_quantity')
            ->label('Количество на складе')
            ->numeric()
            ->required()
            ->default(0)
            ->minValue(0);
    }

    /**
     * Создать поле минимального остатка
     */
    private function createLowStockThresholdField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('low_stock_threshold')
            ->label('Минимальный остаток')
            ->numeric()
            ->default(5)
            ->minValue(0);
    }

    /**
     * Создать поле отслеживания остатков
     */
    private function createTrackStockField(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('track_stock')
            ->label('Отслеживать остатки')
            ->default(true);
    }

    /**
     * Создать поле активности
     */
    private function createIsActiveField(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('is_active')
            ->label('Активен')
            ->default(true);
    }

    /**
     * Создать поле рекомендуемого товара
     */
    private function createIsFeaturedField(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('is_featured')
            ->label('Рекомендуемый')
            ->default(false);
    }

    /**
     * Создать поле цифрового товара
     */
    private function createIsDigitalField(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('is_digital')
            ->label('Цифровой товар')
            ->default(false);
    }

    /**
     * Создать поле веса
     */
    private function createWeightField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('weight')
            ->label('Вес (г)')
            ->numeric()
            ->minValue(0);
    }

    /**
     * Создать поле размеров
     */
    private function createDimensionsField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('dimensions')
            ->label('Размеры (ДxШxВ)')
            ->helperText('Например: 10x5x2');
    }
}
