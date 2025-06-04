<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Services\Forms\ProductFormFieldFactory;
use App\Services\Tables\ProductTableComponentsFactory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;


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
            ->schema(static::getFormFieldFactory()->createMainLayout());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableFactory()->createTableColumns())
            ->filters(static::getTableFactory()->createTableFilters())
            ->actions(static::getTableFactory()->createRowActions())
            ->bulkActions(static::getTableFactory()->createBulkActions())
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
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    /**
     * Получить фабрику полей формы
     */
    private static function getFormFieldFactory(): ProductFormFieldFactory
    {
        return app(ProductFormFieldFactory::class);
    }

    /**
     * Получить фабрику элементов таблицы
     */
    private static function getTableFactory(): ProductTableComponentsFactory
    {
        return app(ProductTableComponentsFactory::class);
    }
}
