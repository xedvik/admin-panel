<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Services\Forms\AttributeFormFieldFactory;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Основная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Название'),

                        Infolists\Components\TextEntry::make('sku')
                            ->label('Артикул'),

                        Infolists\Components\TextEntry::make('category.name')
                            ->label('Категория'),

                        Infolists\Components\TextEntry::make('short_description')
                            ->label('Краткое описание'),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Описание')
                            ->html(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Ценообразование и остатки')
                    ->schema([
                        Infolists\Components\TextEntry::make('price')
                            ->label('Цена')
                            ->money('RUB'),

                        Infolists\Components\TextEntry::make('compare_price')
                            ->label('Цена до скидки')
                            ->money('RUB')
                            ->visible(fn ($record) => $record->compare_price),

                        Infolists\Components\TextEntry::make('stock_quantity')
                            ->label('Количество на складе')
                            ->visible(fn ($record) => $record->track_quantity),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Активен')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('is_featured')
                            ->label('Рекомендуемый')
                            ->boolean(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Изображения')
                    ->schema([
                        Infolists\Components\ImageEntry::make('images')
                            ->label('Изображения товара')
                            ->visible(fn ($record) => !empty($record->images)),
                    ])
                    ->visible(fn ($record) => !empty($record->images)),

                Infolists\Components\Section::make('Атрибуты товара')
                    ->schema(function ($record) {
                        $fieldFactory = app(AttributeFormFieldFactory::class);
                        return $fieldFactory->createInfolistEntriesForProduct($record->id);
                    })
                    ->visible(function ($record) {
                        $fieldFactory = app(AttributeFormFieldFactory::class);
                        $entries = $fieldFactory->createInfolistEntriesForProduct($record->id);
                        return !empty($entries);
                    }),
            ]);
    }
}
