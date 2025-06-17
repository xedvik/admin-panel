<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Models\Promotion;
use App\Services\Forms\PromotionFormFieldFactory;
use App\Services\Tables\PromotionTableElementsFactory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Акции';

    protected static ?string $modelLabel = 'Акция';

    protected static ?string $pluralModelLabel = 'Акции';

    protected static ?string $navigationGroup = 'Акции и маркетинг';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormFieldFactory()->createMainLayout());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableElementsFactory()->createTableColumns())
            ->filters(static::getTableElementsFactory()->createTableFilters())
            ->actions(static::getTableElementsFactory()->createRowActions())
            ->bulkActions(static::getTableElementsFactory()->createBulkActions())
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
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }

    /**
     * Получить фабрику полей формы
     */
    private static function getFormFieldFactory(): PromotionFormFieldFactory
    {
        return app(PromotionFormFieldFactory::class);
    }

    /**
     * Получить фабрику элементов таблицы
     */
    private static function getTableElementsFactory(): PromotionTableElementsFactory
    {
        return app(PromotionTableElementsFactory::class);
    }
}
