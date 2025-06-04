<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Services\Forms\ProductInCategoryFormFieldFactory;
use App\Services\Tables\ProductInCategoryTableElementsFactory;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Товары в категории';

    protected static ?string $modelLabel = 'Товар';

    protected static ?string $pluralModelLabel = 'Товары';

    public function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormFieldFactory()->createFullFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(static::getTableElementsFactory()->createTableColumns())
            ->filters(static::getTableElementsFactory()->createTableFilters())
            ->headerActions(static::getTableElementsFactory()->createHeaderActions())
            ->actions(static::getTableElementsFactory()->createRowActions())
            ->bulkActions(static::getTableElementsFactory()->createBulkActions())
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Получить фабрику полей формы
     */
    private static function getFormFieldFactory(): ProductInCategoryFormFieldFactory
    {
        return app(ProductInCategoryFormFieldFactory::class);
    }

    /**
     * Получить фабрику элементов таблицы
     */
    private static function getTableElementsFactory(): ProductInCategoryTableElementsFactory
    {
        return app(ProductInCategoryTableElementsFactory::class);
    }
}
