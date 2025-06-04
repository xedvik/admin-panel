<?php

namespace App\Services\Forms;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductAttributeValueRepositoryInterface;
use Filament\Forms;

class OrderItemFormFieldFactory
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ProductAttributeValueRepositoryInterface $attributeValueRepository
    ) {}

    /**
     * Создать полную схему формы позиции заказа
     */
    public function createFullFormSchema(): array
    {
        return [
            $this->createProductSelectField(),
            $this->createProductInfoFields(),
            $this->createQuantityAndPriceFields(),
            $this->createProductVariantField(),
            $this->createNotesField(),
        ];
    }

    /**
     * Создать поле выбора товара
     */
    public function createProductSelectField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('product_id')
            ->label('Товар')
            ->options(function () {
                return $this->productRepository->getActive()
                    ->pluck('name', 'id')
                    ->map(function ($name, $id) {
                        $product = $this->productRepository->find($id);
                        return "{$product->name} ({$product->sku}) - {$product->price}₽";
                    });
            })
            ->searchable()
            ->preload()
            ->required()
            ->live(onBlur: true)
            ->afterStateUpdated(function (Forms\Set $set, $state) {
                if ($state) {
                    $product = $this->productRepository->find($state);
                    if ($product) {
                        $set('product_name', $product->name);
                        $set('product_sku', $product->sku);
                        $set('product_price', $product->price);
                        $set('total_price', $product->price);

                        // Автозаполнение варианта товара из атрибутов
                        $attributes = $this->attributeValueRepository->getByProduct($product->id);
                        if ($attributes->isNotEmpty()) {
                            $variant = $attributes->map(function ($attributeValue) {
                                return $attributeValue->attribute->name . ': ' . $attributeValue->value;
                            })->join(', ');

                            $set('product_variant', $variant);
                        }
                    }
                }
            });
    }

    /**
     * Создать поля информации о товаре
     */
    public function createProductInfoFields(): Forms\Components\Grid
    {
        return Forms\Components\Grid::make(2)
            ->schema([
                $this->createProductNameField(),
                $this->createProductSkuField(),
            ]);
    }

    /**
     * Создать поля количества и цены
     */
    public function createQuantityAndPriceFields(): Forms\Components\Grid
    {
        return Forms\Components\Grid::make(3)
            ->schema([
                $this->createQuantityField(),
                $this->createProductPriceField(),
                $this->createTotalPriceField(),
            ]);
    }

    /**
     * Создать поле названия товара
     */
    private function createProductNameField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('product_name')
            ->label('Название товара')
            ->disabled()
            ->dehydrated(true);
    }

    /**
     * Создать поле артикула товара
     */
    private function createProductSkuField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('product_sku')
            ->label('Артикул')
            ->disabled()
            ->dehydrated(true);
    }

    /**
     * Создать поле количества
     */
    private function createQuantityField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('quantity')
            ->label('Количество')
            ->numeric()
            ->required()
            ->default(1)
            ->minValue(1)
            ->live(onBlur: true)
            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                $price = $get('product_price') ?? 0;
                $set('total_price', $price * ($state ?? 1));
            });
    }

    /**
     * Создать поле цены за единицу
     */
    private function createProductPriceField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('product_price')
            ->label('Цена за единицу')
            ->numeric()
            ->required()
            ->prefix('₽')
            ->live(onBlur: true)
            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                $quantity = $get('quantity') ?? 1;
                $set('total_price', ($state ?? 0) * $quantity);
            });
    }

    /**
     * Создать поле общей стоимости
     */
    private function createTotalPriceField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('total_price')
            ->label('Общая стоимость')
            ->numeric()
            ->disabled()
            ->dehydrated(true)
            ->prefix('₽');
    }

    /**
     * Создать поле варианта товара
     */
    public function createProductVariantField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('product_variant')
            ->label('Вариант товара')
            ->placeholder('Например: Цвет: Красный, Размер: L, Материал: Хлопок')
            ->helperText('Укажите особенности товара (цвет, размер, материал и т.д.)')
            ->rows(2)
            ->columnSpanFull();
    }

    /**
     * Создать поле примечаний
     */
    public function createNotesField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('notes')
            ->label('Примечания')
            ->rows(2)
            ->columnSpanFull();
    }
}
