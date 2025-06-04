<?php

namespace App\Services\Forms;

use App\Services\Forms\AttributeFormFieldFactory;
use Filament\Forms;
use Illuminate\Support\Str;

class ProductFormFieldFactory
{
    public function __construct(
        private AttributeFormFieldFactory $attributeFormFieldFactory
    ) {}

    /**
     * Создать основные поля товара (название, slug, sku, описания)
     */
    public function createBaseFields(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Название товара')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                    $operation === 'create' ? $set('slug', Str::slug($state)) : null
                ),

            Forms\Components\TextInput::make('slug')
                ->label('URL (slug)')
                ->required()
                ->maxLength(255)
                ->unique('products', 'slug', ignoreRecord: true)
                ->rules(['alpha_dash'])
                ->helperText('Используется в URL адресе'),

            Forms\Components\Select::make('category_id')
                ->label('Категория')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('sku')
                ->label('Артикул (SKU)')
                ->unique('products', 'sku', ignoreRecord: true)
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('short_description')
                ->label('Краткое описание')
                ->rows(3),

            Forms\Components\RichEditor::make('description')
                ->label('Полное описание')
                ->columnSpanFull(),
        ];
    }

    /**
     * Создать поля настроек и статуса
     */
    public function createStatusFields(): array
    {
        return [
            Forms\Components\Toggle::make('is_active')
                ->label('Активен')
                ->default(true),

            Forms\Components\Toggle::make('is_featured')
                ->label('Рекомендуемый')
                ->default(false),

            Forms\Components\DateTimePicker::make('published_at')
                ->label('Дата публикации')
                ->default(now()),
        ];
    }

    /**
     * Создать поля ценообразования
     */
    public function createPricingFields(): array
    {
        return [
            Forms\Components\TextInput::make('price')
                ->label('Цена (руб.)')
                ->required()
                ->numeric()
                ->minValue(0)
                ->prefix('₽'),

            Forms\Components\TextInput::make('compare_price')
                ->label('Цена до скидки (руб.)')
                ->numeric()
                ->minValue(0)
                ->prefix('₽')
                ->helperText('Зачеркнутая цена для показа скидки'),
        ];
    }

    /**
     * Создать поля склада и доставки
     */
    public function createInventoryFields(): array
    {
        return [
            Forms\Components\Toggle::make('track_quantity')
                ->label('Отслеживать остатки')
                ->default(true)
                ->live(),

            Forms\Components\TextInput::make('stock_quantity')
                ->label('Количество на складе')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->visible(fn (Forms\Get $get) => $get('track_quantity')),

            Forms\Components\Toggle::make('continue_selling_when_out_of_stock')
                ->label('Продавать при нулевом остатке')
                ->default(false)
                ->visible(fn (Forms\Get $get) => $get('track_quantity')),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('weight')
                        ->label('Вес')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01),

                    Forms\Components\Select::make('weight_unit')
                        ->label('Единица')
                        ->options([
                            'g' => 'г',
                            'kg' => 'кг',
                        ])
                        ->default('kg'),
                ]),
        ];
    }

    /**
     * Создать поле изображений
     */
    public function createImageField(): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make('images')
            ->label('Изображения товара')
            ->image()
            ->multiple()
            ->reorderable()
            ->imageEditor()
            ->directory('products')
            ->maxFiles(10)
            ->helperText('Первое изображение будет основным');
    }

    /**
     * Создать поля SEO
     */
    public function createSeoFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('SEO')
            ->schema([
                Forms\Components\TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->maxLength(255)
                    ->helperText('Заголовок для поисковых систем'),

                Forms\Components\Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->rows(2)
                    ->maxLength(255)
                    ->helperText('Описание для поисковых систем (до 255 символов)'),
            ])
            ->collapsed()
            ->columnSpanFull();
    }

    /**
     * Создать секцию атрибутов товара
     */
    public function createAttributesSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Атрибуты товара')
            ->schema($this->attributeFormFieldFactory->createFieldsForActiveAttributes())
            ->collapsed()
            ->columnSpanFull();
    }

    /**
     * Создать основной макет формы
     */
    public function createMainLayout(): array
    {
        return [
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Section::make('Основная информация')
                        ->schema($this->createBaseFields())
                        ->columnSpan(2),

                    Forms\Components\Section::make('Настройки и статус')
                        ->schema($this->createStatusFields())
                        ->columnSpan(1),
                ]),

            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Section::make('Ценообразование')
                        ->schema($this->createPricingFields())
                        ->columnSpan(1),

                    Forms\Components\Section::make('Склад и доставка')
                        ->schema($this->createInventoryFields())
                        ->columnSpan(1),

                    Forms\Components\Section::make('Изображения')
                        ->schema([$this->createImageField()])
                        ->columnSpan(1),
                ]),

            $this->createSeoFields(),
            $this->createAttributesSection(),
        ];
    }
}
