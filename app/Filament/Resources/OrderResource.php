<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Services\Forms\OrderFormFieldFactory;
use App\Services\Tables\OrderTableElementsFactory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Заказы';

    protected static ?string $modelLabel = 'Заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormFieldFactory()->createMainLayout());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['client', 'orderItems']))
            ->columns(static::getTableElementsFactory()->createTableColumns())
            ->filters(static::getTableElementsFactory()->createTableFilters())
            ->actions(static::getTableElementsFactory()->createRowActions())
            ->bulkActions(static::getTableElementsFactory()->createBulkActions())
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /**
     * Получить фабрику полей формы
     */
    private static function getFormFieldFactory(): OrderFormFieldFactory
    {
        return app(OrderFormFieldFactory::class);
    }

    /**
     * Получить фабрику элементов таблицы
     */
    private static function getTableElementsFactory(): OrderTableElementsFactory
    {
        return app(OrderTableElementsFactory::class);
    }
}
