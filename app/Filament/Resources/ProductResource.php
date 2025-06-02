<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

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
                                    ->unique(Product::class, 'slug', ignoreRecord: true)
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
                                    ->unique(Product::class, 'sku', ignoreRecord: true)
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Фото')
                    ->getStateUsing(fn (?Product $record) => $record?->main_image)
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

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Скидка')
                    ->getStateUsing(fn (?Product $record) => $record?->discount_percentage)
                    ->suffix('%')
                    ->badge()
                    ->color('success')
                    ->visible(fn (?Product $record) => $record && $record->discount_percentage > 0),

                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Остатки')
                    ->getStateUsing(fn (?Product $record) => $record?->stock_status)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'В наличии' => 'success',
                        'Мало' => 'warning',
                        'Нет в наличии' => 'danger',
                        default => 'gray',
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
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Рекомендуемые')
                    ->boolean()
                    ->trueLabel('Только рекомендуемые')
                    ->falseLabel('Только обычные')
                    ->native(false),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Нет в наличии')
                    ->query(fn (Builder $query): Builder =>
                        $query->where('track_quantity', true)
                              ->where('stock_quantity', '<=', 0)
                              ->where('continue_selling_when_out_of_stock', false)
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Просмотр'),
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить'),
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
