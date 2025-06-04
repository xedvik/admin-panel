<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductAttributeResource\Pages;
use App\Models\ProductAttribute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductAttributeResource extends Resource
{
    protected static ?string $model = ProductAttribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Атрибуты товаров';

    protected static ?string $modelLabel = 'Атрибут товара';

    protected static ?string $pluralModelLabel = 'Атрибуты товаров';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основные данные')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, $state, Forms\Set $set) {
                                if ($context === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ProductAttribute::class, 'slug', ignoreRecord: true)
                            ->rules(['alpha_dash']),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Тип и настройки')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Тип атрибута')
                            ->required()
                            ->options([
                                'text' => 'Текст',
                                'number' => 'Число',
                                'select' => 'Выбор из списка',
                                'boolean' => 'Да/Нет',
                                'date' => 'Дата',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state !== 'select') {
                                    $set('options', null);
                                }
                            }),

                        Forms\Components\TagsInput::make('options')
                            ->label('Варианты (для типа "Выбор из списка")')
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'select')
                            ->helperText('Введите варианты для выбора. Нажмите Enter после каждого варианта.'),

                        Forms\Components\Toggle::make('is_required')
                            ->label('Обязательный атрибут')
                            ->default(false),

                        Forms\Components\Toggle::make('is_filterable')
                            ->label('Использовать в фильтрах')
                            ->default(true),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активный')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->helperText('Меньшее число = выше в списке'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'text' => 'Текст',
                        'number' => 'Число',
                        'select' => 'Выбор',
                        'boolean' => 'Да/Нет',
                        'date' => 'Дата',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'text' => 'gray',
                        'number' => 'blue',
                        'select' => 'green',
                        'boolean' => 'orange',
                        'date' => 'purple',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('values_count')
                    ->label('Товаров с атрибутом')
                    ->counts('values')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Обязательный')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_filterable')
                    ->label('В фильтрах')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активный')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'text' => 'Текст',
                        'number' => 'Число',
                        'select' => 'Выбор',
                        'boolean' => 'Да/Нет',
                        'date' => 'Дата',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активный'),

                Tables\Filters\TernaryFilter::make('is_required')
                    ->label('Обязательный'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductAttributes::route('/'),
            'create' => Pages\CreateProductAttribute::route('/create'),
            'edit' => Pages\EditProductAttribute::route('/{record}/edit'),
        ];
    }
}
