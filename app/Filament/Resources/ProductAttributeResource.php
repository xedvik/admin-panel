<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductAttributeResource\Pages;
use App\Models\ProductAttribute;
use App\Services\Forms\ProductAttributeFormFieldFactory;
use App\Services\Tables\ProductAttributeTableComponentsFactory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ProductAttributeResource extends Resource
{
    protected static ?string $model = ProductAttribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Атрибуты товаров';

    protected static ?string $modelLabel = 'Атрибут товара';

    protected static ?string $pluralModelLabel = 'Атрибуты товаров';

    protected static ?string $navigationGroup = 'Атрибуты';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormFieldFactory()->createMainLayout());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableComponentsFactory()->createTableColumns())
            ->filters(static::getTableComponentsFactory()->createTableFilters())
            ->actions(static::getTableComponentsFactory()->createRowActions())
            ->bulkActions(static::getTableComponentsFactory()->createBulkActions())
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

    /**
     * Получить фабрику полей формы
     */
    private static function getFormFieldFactory(): ProductAttributeFormFieldFactory
    {
        return app(ProductAttributeFormFieldFactory::class);
    }

    /**
     * Получить фабрику элементов таблицы
     */
    private static function getTableComponentsFactory(): ProductAttributeTableComponentsFactory
    {
        return app(ProductAttributeTableComponentsFactory::class);
    }
}
