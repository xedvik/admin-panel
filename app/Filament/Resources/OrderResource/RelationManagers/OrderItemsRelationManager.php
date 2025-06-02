<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $title = 'Позиции заказа';

    protected static ?string $modelLabel = 'Позиция';

    protected static ?string $pluralModelLabel = 'Позиции';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Товар')
                    ->relationship('product', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Product $record) => "{$record->name} ({$record->sku}) - {$record->price}₽")
                    ->searchable(['name', 'sku'])
                    ->preload()
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($state) {
                            $product = Product::find($state);
                            if ($product) {
                                $set('product_name', $product->name);
                                $set('product_sku', $product->sku);
                                $set('price', $product->price);
                                $set('total_price', $product->price);
                            }
                        }
                    }),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('product_name')
                            ->label('Название товара')
                            ->disabled()
                            ->dehydrated(true),

                        Forms\Components\TextInput::make('product_sku')
                            ->label('Артикул')
                            ->disabled()
                            ->dehydrated(true),
                    ]),

                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                $price = $get('price') ?? 0;
                                $set('total_price', $price * ($state ?? 1));
                            }),

                        Forms\Components\TextInput::make('price')
                            ->label('Цена за единицу')
                            ->numeric()
                            ->required()
                            ->prefix('₽')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                $quantity = $get('quantity') ?? 1;
                                $set('total_price', ($state ?? 0) * $quantity);
                            }),

                        Forms\Components\TextInput::make('total_price')
                            ->label('Общая стоимость')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true)
                            ->prefix('₽'),
                    ]),

                Forms\Components\Textarea::make('product_variant')
                    ->label('Вариант товара (JSON)')
                    ->helperText('Например: {"color": "red", "size": "M"}')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('notes')
                    ->label('Примечания')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                Tables\Columns\ImageColumn::make('product.images')
                    ->label('Фото')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText(),

                Tables\Columns\TextColumn::make('product_name')
                    ->label('Товар')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->product_sku ? "Артикул: {$record->product_sku}" : null),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('product_variant')
                    ->label('Вариант')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state) && !empty($state)) {
                            return collect($state)->map(fn ($value, $key) => "{$key}: {$value}")->join(', ');
                        }
                        return '-';
                    })
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Примечания')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Товар')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить товар'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Изменить'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('id');
    }
}
