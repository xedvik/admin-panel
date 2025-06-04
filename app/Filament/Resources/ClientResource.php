<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use App\Services\Forms\ClientFormFieldFactory;
use App\Services\Tables\ClientTableElementsFactory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Клиенты';

    protected static ?string $modelLabel = 'Клиент';

    protected static ?string $pluralModelLabel = 'Клиенты';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema(static::getFormFieldFactory()->createFullFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('clientAddresses'))
            ->columns(static::getTableElementsFactory()->createTableColumns())
            ->filters(static::getTableElementsFactory()->createTableFilters())
            ->actions(static::getTableElementsFactory()->createStandardActions())
            ->bulkActions([static::getTableElementsFactory()->createBulkActions()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    /**
     * Получить фабрику полей формы
     */
    private static function getFormFieldFactory(): ClientFormFieldFactory
    {
        return app(ClientFormFieldFactory::class);
    }

    /**
     * Получить фабрику элементов таблицы
     */
    private static function getTableElementsFactory(): ClientTableElementsFactory
    {
        return app(ClientTableElementsFactory::class);
    }
}
