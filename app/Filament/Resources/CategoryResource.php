<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Filament\Resources\ProductResource;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Services\Forms\CategoryFormFieldFactory;
use App\Services\Tables\CategoryTableElementsFactory;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Категории';

    protected static ?string $modelLabel = 'Категория';

    protected static ?string $pluralModelLabel = 'Категории';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make('Основная информация')
                            ->schema([
                                ...static::getFormFieldFactory()->createBaseFields(),
                                static::getFormFieldFactory()->createImageField(),
                            ])
                            ->columnSpan(1),

                        Forms\Components\Section::make('Настройки')
                            ->schema([
                                static::getFormFieldFactory()->createParentCategoryField(),
                                static::getFormFieldFactory()->createSortOrderField(),
                                static::getFormFieldFactory()->createActiveField(),
                            ])
                            ->columnSpan(1),
                    ]),

                static::getFormFieldFactory()->createSeoFields(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableElementsFactory()->createTableColumns())
            ->filters(static::getTableElementsFactory()->createTableFilters())
            ->actions(static::getTableElementsFactory()->createRowActions())
            ->bulkActions([
                static::getTableElementsFactory()->createStandardBulkActions(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    /**
     * Получить фабрику полей формы
     */
    private static function getFormFieldFactory(): CategoryFormFieldFactory
    {
        return app(CategoryFormFieldFactory::class);
    }

    /**
     * Получить фабрику элементов таблицы
     */
    private static function getTableElementsFactory(): CategoryTableElementsFactory
    {
        return app(CategoryTableElementsFactory::class);
    }
}
