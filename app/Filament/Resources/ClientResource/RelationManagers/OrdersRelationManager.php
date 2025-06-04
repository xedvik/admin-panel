<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Services\Forms\OrderForClientFormFieldFactory;
use App\Services\Tables\OrderForClientTableElementsFactory;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Заказы клиента';

    protected static ?string $modelLabel = 'Заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    public function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormFieldFactory()->createFullFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
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
    private static function getFormFieldFactory(): OrderForClientFormFieldFactory
    {
        return app(OrderForClientFormFieldFactory::class);
    }

    /**
     * Получить фабрику элементов таблицы
     */
    private static function getTableElementsFactory(): OrderForClientTableElementsFactory
    {
        return app(OrderForClientTableElementsFactory::class);
    }
}
