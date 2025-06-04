<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Services\Forms\OrderItemFormFieldFactory;
use App\Services\Tables\OrderItemTableElementsFactory;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $title = 'Позиции заказа';

    protected static ?string $modelLabel = 'Позиция';

    protected static ?string $pluralModelLabel = 'Позиции';

    public function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormFieldFactory()->createFullFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product']))
            ->columns(static::getTableElementsFactory()->createTableColumns())
            ->filters(static::getTableElementsFactory()->createTableFilters())
            ->headerActions(static::getTableElementsFactory()->createHeaderActions())
            ->actions(static::getTableElementsFactory()->createRowActions())
            ->bulkActions(static::getTableElementsFactory()->createBulkActions())
            ->defaultSort('id');
    }

    /**
     * Получить фабрику полей формы
     */
    private static function getFormFieldFactory(): OrderItemFormFieldFactory
    {
        return app(OrderItemFormFieldFactory::class);
    }

    /**
     * Получить фабрику элементов таблицы
     */
    private static function getTableElementsFactory(): OrderItemTableElementsFactory
    {
        return app(OrderItemTableElementsFactory::class);
    }
}
