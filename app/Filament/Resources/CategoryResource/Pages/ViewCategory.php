<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Основная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Название'),

                        Infolists\Components\TextEntry::make('slug')
                            ->label('URL (slug)'),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Описание')
                            ->columnSpanFull(),

                        Infolists\Components\ImageEntry::make('image')
                            ->label('Изображение')
                            ->visible(fn ($record) => !empty($record->image)),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Иерархия и настройки')
                    ->schema([
                        Infolists\Components\TextEntry::make('parent.name')
                            ->label('Родительская категория')
                            ->visible(fn ($record) => $record->parent_id),

                        Infolists\Components\TextEntry::make('sort_order')
                            ->label('Порядок сортировки'),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Активна')
                            ->boolean(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Статистика')
                    ->schema([
                        Infolists\Components\TextEntry::make('products_count')
                            ->label('Количество товаров')
                            ->state(fn ($record) => $record->products()->count()),

                        Infolists\Components\TextEntry::make('children_count')
                            ->label('Подкатегорий')
                            ->state(fn ($record) => $record->children()->count()),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('SEO')
                    ->schema([
                        Infolists\Components\TextEntry::make('meta_title')
                            ->label('Meta Title')
                            ->visible(fn ($record) => !empty($record->meta_title)),

                        Infolists\Components\TextEntry::make('meta_description')
                            ->label('Meta Description')
                            ->visible(fn ($record) => !empty($record->meta_description)),
                    ])
                    ->visible(fn ($record) => !empty($record->meta_title) || !empty($record->meta_description))
                    ->collapsed(),

                Infolists\Components\Section::make('Системная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Создана')
                            ->dateTime('d.m.Y H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Обновлена')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
