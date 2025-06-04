<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductAttributeRepositoryInterface;
use App\Contracts\Repositories\ProductAttributeValueRepositoryInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Товары';

    protected static ?string $modelLabel = 'Товар';

    protected static ?string $pluralModelLabel = 'Товары';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('Основная информация')
                            ->schema([
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
                            ])
                            ->columnSpan(2),

                        Forms\Components\Section::make('Настройки и статус')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Активен')
                                    ->default(true),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Рекомендуемый')
                                    ->default(false),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Дата публикации')
                                    ->default(now()),
                            ])
                            ->columnSpan(1),
                    ]),

                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('Ценообразование')
                            ->schema([
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
                            ])
                            ->columnSpan(1),

                        Forms\Components\Section::make('Склад и доставка')
                            ->schema([
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
                            ])
                            ->columnSpan(1),

                        Forms\Components\Section::make('Изображения')
                            ->schema([
                                Forms\Components\FileUpload::make('images')
                                    ->label('Изображения товара')
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->imageEditor()
                                    ->directory('products')
                                    ->maxFiles(10)
                                    ->helperText('Первое изображение будет основным'),
                            ])
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('SEO')
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
                    ->columnSpanFull(),

                Forms\Components\Section::make('Атрибуты товара')
                    ->schema(function () {
                        $attributeRepository = app(ProductAttributeRepositoryInterface::class);
                        $attributes = $attributeRepository->getActive();

                        if ($attributes->isEmpty()) {
                            return [
                                Forms\Components\Placeholder::make('no_attributes')
                                    ->label('Атрибуты не настроены')
                                    ->content('Сначала создайте атрибуты товаров в разделе "Атрибуты товаров"'),
                            ];
                        }

                        $fields = [];

                        foreach ($attributes as $attribute) {
                            switch ($attribute->type) {
                                case 'text':
                                    $fields[] = Forms\Components\TextInput::make("attribute_{$attribute->id}")
                                        ->label($attribute->name)
                                        ->maxLength(255)
                                        ->helperText($attribute->description)
                                        ->dehydrated(false);
                                    break;

                                case 'number':
                                    $fields[] = Forms\Components\TextInput::make("attribute_{$attribute->id}")
                                        ->label($attribute->name)
                                        ->numeric()
                                        ->helperText($attribute->description)
                                        ->dehydrated(false);
                                    break;

                                case 'select':
                                    if (!empty($attribute->options)) {
                                        $options = array_combine($attribute->options, $attribute->options);
                                        $fields[] = Forms\Components\Select::make("attribute_{$attribute->id}")
                                            ->label($attribute->name)
                                            ->options($options)
                                            ->searchable()
                                            ->helperText($attribute->description)
                                            ->dehydrated(false);
                                    }
                                    break;

                                case 'boolean':
                                    $fields[] = Forms\Components\Toggle::make("attribute_{$attribute->id}")
                                        ->label($attribute->name)
                                        ->helperText($attribute->description)
                                        ->dehydrated(false);
                                    break;

                                case 'date':
                                    $fields[] = Forms\Components\DatePicker::make("attribute_{$attribute->id}")
                                        ->label($attribute->name)
                                        ->helperText($attribute->description)
                                        ->dehydrated(false);
                                    break;
                            }
                        }

                        return $fields;
                    })
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Фото')
                    ->getStateUsing(function (?Product $record) {
                        if (!$record) return null;
                        $productRepository = app(ProductRepositoryInterface::class);
                        return $productRepository->getMainImage($record);
                    })
                    ->size(50),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->description(fn (?Product $record): string => $record?->sku ?? ''),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Количество')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(function (?Product $record): string {
                        if (!$record || !$record->track_quantity) return 'gray';
                        $quantity = $record->stock_quantity ?? 0;
                        if ($quantity <= 0) return 'danger';
                        if ($quantity <= 5) return 'warning';
                        return 'success';
                    })
                    ->formatStateUsing(function (?Product $record): string {
                        if (!$record || !$record->track_quantity) {
                            return 'Не отслеживается';
                        }
                        return (string) ($record->stock_quantity ?? 0);
                    }),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Скидка')
                    ->getStateUsing(function (?Product $record) {
                        if (!$record) return 0;
                        $productRepository = app(ProductRepositoryInterface::class);
                        return $productRepository->calculateDiscountPercent($record);
                    })
                    ->suffix('%')
                    ->badge()
                    ->color('success')
                    ->visible(function (?Product $record) {
                        if (!$record) return false;
                        $productRepository = app(ProductRepositoryInterface::class);
                        return $productRepository->calculateDiscountPercent($record) > 0;
                    }),

                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Остатки')
                    ->getStateUsing(function (?Product $record) {
                        if (!$record) return '';
                        $productRepository = app(ProductRepositoryInterface::class);
                        return $productRepository->getStockStatus($record);
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'В наличии' => 'success',
                            'Мало в наличии' => 'warning',
                            'Нет в наличии' => 'danger',
                            'Под заказ' => 'info',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Рекомендуемый')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные')
                    ->boolean()
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === true) {
                            $productRepository = app(ProductRepositoryInterface::class);
                            $productIds = $productRepository->getActive()->pluck('id');
                            return $query->whereIn('id', $productIds);
                        } elseif ($data['value'] === false) {
                            $productRepository = app(ProductRepositoryInterface::class);
                            $activeIds = $productRepository->getActive()->pluck('id');
                            return $query->whereNotIn('id', $activeIds);
                        }
                        return $query;
                    })
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Рекомендуемые')
                    ->boolean()
                    ->trueLabel('Только рекомендуемые')
                    ->falseLabel('Только обычные')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === true) {
                            $productRepository = app(ProductRepositoryInterface::class);
                            $productIds = $productRepository->getFeatured()->pluck('id');
                            return $query->whereIn('id', $productIds);
                        } elseif ($data['value'] === false) {
                            $productRepository = app(ProductRepositoryInterface::class);
                            $featuredIds = $productRepository->getFeatured()->pluck('id');
                            return $query->whereNotIn('id', $featuredIds);
                        }
                        return $query;
                    })
                    ->native(false),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Нет в наличии')
                    ->query(function (Builder $query): Builder {
                        $productRepository = app(ProductRepositoryInterface::class);
                        $outOfStockIds = $productRepository->getOutOfStockProducts()->pluck('id');
                        return $query->whereIn('id', $outOfStockIds);
                    }),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Мало в наличии')
                    ->query(function (Builder $query): Builder {
                        $productRepository = app(ProductRepositoryInterface::class);
                        $lowStockIds = $productRepository->getLowStockProducts()->pluck('id');
                        return $query->whereIn('id', $lowStockIds);
                    }),

                Tables\Filters\Filter::make('in_stock')
                    ->label('В наличии')
                    ->query(function (Builder $query): Builder {
                        $productRepository = app(ProductRepositoryInterface::class);
                        $inStockIds = $productRepository->getInStock()->pluck('id');
                        return $query->whereIn('id', $inStockIds);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Просмотр'),

                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),

                Tables\Actions\Action::make('updateStock')
                    ->label('Обновить остатки')
                    ->icon('heroicon-o-cube')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Новое количество')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $productRepository = app(ProductRepositoryInterface::class);
                        $productRepository->updateStock($record->id, $data['stock_quantity']);

                        Notification::make()
                            ->title('Остатки обновлены')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('addStock')
                    ->label('Добавить остатки')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество для добавления')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $productRepository = app(ProductRepositoryInterface::class);
                        $productRepository->incrementStock($record->id, $data['quantity']);

                        Notification::make()
                            ->title("Добавлено {$data['quantity']} единиц товара")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить'),

                    Tables\Actions\BulkAction::make('activateProducts')
                        ->label('Активировать товары')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $productRepository = app(ProductRepositoryInterface::class);
                            $count = 0;

                            foreach ($records as $record) {
                                $productRepository->update($record->id, ['is_active' => true]);
                                $count++;
                            }

                            Notification::make()
                                ->title("Активировано товаров: {$count}")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivateProducts')
                        ->label('Деактивировать товары')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $productRepository = app(ProductRepositoryInterface::class);
                            $count = 0;

                            foreach ($records as $record) {
                                $productRepository->update($record->id, ['is_active' => false]);
                                $count++;
                            }

                            Notification::make()
                                ->title("Деактивировано товаров: {$count}")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('markAsFeatured')
                        ->label('Сделать рекомендуемыми')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $productRepository = app(ProductRepositoryInterface::class);
                            $count = 0;

                            foreach ($records as $record) {
                                $productRepository->update($record->id, ['is_featured' => true]);
                                $count++;
                            }

                            Notification::make()
                                ->title("Отмечено как рекомендуемые: {$count} товаров")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
