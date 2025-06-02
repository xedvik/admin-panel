<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Товары в категории';

    protected static ?string $modelLabel = 'Товар';

    protected static ?string $pluralModelLabel = 'Товары';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Название товара')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str($state)->slug())),

                                Forms\Components\TextInput::make('slug')
                                    ->label('URL (slug)')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('sku')
                                    ->label('Артикул')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100),

                                Forms\Components\Select::make('category_id')
                                    ->label('Категория')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Цены и остатки')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Цена')
                                    ->numeric()
                                    ->required()
                                    ->prefix('₽')
                                    ->minValue(0),

                                Forms\Components\TextInput::make('compare_price')
                                    ->label('Цена без скидки')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->minValue(0),

                                Forms\Components\TextInput::make('cost_price')
                                    ->label('Себестоимость')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->minValue(0),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('stock_quantity')
                                    ->label('Количество на складе')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->minValue(0),

                                Forms\Components\TextInput::make('low_stock_threshold')
                                    ->label('Минимальный остаток')
                                    ->numeric()
                                    ->default(5)
                                    ->minValue(0),

                                Forms\Components\Toggle::make('track_stock')
                                    ->label('Отслеживать остатки')
                                    ->default(true),
                            ]),
                    ]),

                Forms\Components\Section::make('Статус и настройки')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Активен')
                                    ->default(true),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Рекомендуемый')
                                    ->default(false),

                                Forms\Components\Toggle::make('is_digital')
                                    ->label('Цифровой товар')
                                    ->default(false),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('weight')
                                    ->label('Вес (г)')
                                    ->numeric()
                                    ->minValue(0),

                                Forms\Components\TextInput::make('dimensions')
                                    ->label('Размеры (ДxШxВ)')
                                    ->helperText('Например: 10x5x2'),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label('Фото')
                    ->circular()
                    ->stacked()
                    ->limit(3),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->sku ? "Артикул: {$record->sku}" : null),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Остаток')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Рекомендуемый')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
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

                Tables\Filters\Filter::make('low_stock')
                    ->label('Заканчивается')
                    ->query(fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 5)),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Нет в наличии')
                    ->query(fn (Builder $query): Builder => $query->where('stock_quantity', 0)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить товар'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
