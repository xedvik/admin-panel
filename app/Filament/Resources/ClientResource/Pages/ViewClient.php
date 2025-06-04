<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    /**
     * Загружаем отношения для корректного отображения
     */
    protected function resolveRecord($key): \Illuminate\Database\Eloquent\Model
    {
        return static::getResource()::resolveRecordRouteBinding($key)->load('clientAddresses');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Личная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('first_name')
                            ->label('Имя'),

                        Infolists\Components\TextEntry::make('last_name')
                            ->label('Фамилия'),

                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('phone')
                            ->label('Телефон')
                            ->copyable()
                            ->visible(fn ($record) => !empty($record->phone)),

                        Infolists\Components\TextEntry::make('date_of_birth')
                            ->label('Дата рождения')
                            ->date('d.m.Y')
                            ->visible(fn ($record) => !empty($record->date_of_birth)),

                        Infolists\Components\TextEntry::make('gender')
                            ->label('Пол')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'male' => 'Мужской',
                                'female' => 'Женский',
                                default => $state,
                            })
                            ->visible(fn ($record) => !empty($record->gender)),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Статус и настройки')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Активен')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('accepts_marketing')
                            ->label('Согласие на маркетинг')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('email_verified_at')
                            ->label('Email подтвержден')
                            ->boolean()
                            ->getStateUsing(fn ($record) => !is_null($record->email_verified_at)),

                        Infolists\Components\TextEntry::make('email_verified_at')
                            ->label('Дата подтверждения')
                            ->dateTime('d.m.Y H:i')
                            ->visible(fn ($record) => !is_null($record->email_verified_at)),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Статистика')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer_status')
                            ->label('Статус клиента')
                            ->getStateUsing(function ($record) {
                                $clientRepository = app(\App\Contracts\Repositories\ClientRepositoryInterface::class);
                                return $clientRepository->getClientStatus($record->id);
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'VIP' => 'success',
                                'Постоянный' => 'warning',
                                'Обычный' => 'gray',
                                'Новый' => 'info',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('total_orders')
                            ->label('Количество заказов')
                            ->getStateUsing(function ($record) {
                                $clientRepository = app(\App\Contracts\Repositories\ClientRepositoryInterface::class);
                                return $clientRepository->getClientOrdersCount($record->id);
                            }),

                        Infolists\Components\TextEntry::make('total_spent')
                            ->label('Общая сумма покупок')
                            ->getStateUsing(function ($record) {
                                $clientRepository = app(\App\Contracts\Repositories\ClientRepositoryInterface::class);
                                return $clientRepository->getClientTotalSpent($record->id);
                            })
                            ->money('RUB'),
                    ])
                    ->columns(3),

                $this->createAddressesSection(),

                Infolists\Components\Section::make('Системная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Дата регистрации')
                            ->dateTime('d.m.Y H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Последнее обновление')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    /**
     * Создать секцию адресов
     */
    private function createAddressesSection(): Infolists\Components\Section
    {
        return Infolists\Components\Section::make('Адреса клиента')
            ->description('Адреса доставки и оплаты клиента')
            ->schema([
                Infolists\Components\RepeatableEntry::make('clientAddresses')
                    ->label('')
                    ->schema([
                        Infolists\Components\Section::make('')
                            ->schema([
                                // Заголовок адреса - тип, название, основной
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('type')
                                            ->label('Тип адреса')
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                'shipping' => 'Доставка',
                                                'billing' => 'Оплата',
                                                default => $state,
                                            })
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'shipping' => 'info',
                                                'billing' => 'warning',
                                                default => 'gray',
                                            }),

                                        Infolists\Components\TextEntry::make('label')
                                            ->label('Название')
                                            ->visible(fn ($record) => !empty($record->label))
                                            ->badge()
                                            ->color('success')
                                            ->placeholder('—'),

                                        Infolists\Components\IconEntry::make('is_default')
                                            ->label('Основной')
                                            ->boolean()
                                            ->trueIcon('heroicon-o-star')
                                            ->falseIcon('heroicon-o-minus'),
                                    ]),

                                // Получатель и компания в одну строку
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('full_name')
                                            ->label('Получатель')
                                            ->getStateUsing(fn ($record) => trim($record->first_name . ' ' . $record->last_name))
                                            ->weight('bold'),

                                        Infolists\Components\TextEntry::make('company')
                                            ->label('Компания')
                                            ->visible(fn ($record) => !empty($record->company))
                                            ->placeholder('—'),
                                    ]),

                                // Полный адрес
                                Infolists\Components\TextEntry::make('street')
                                    ->label('Адрес')
                                    ->columnSpanFull()
                                    ->weight('medium'),

                                // Все остальные поля в две равные колонки для компактности
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('city')
                                            ->label('Город'),

                                        Infolists\Components\TextEntry::make('state')
                                            ->label('Область/Регион')
                                            ->visible(fn ($record) => !empty($record->state))
                                            ->placeholder('—'),
                                    ]),

                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('postal_code')
                                            ->label('Почтовый индекс')
                                            ->visible(fn ($record) => !empty($record->postal_code))
                                            ->placeholder('—'),

                                        Infolists\Components\TextEntry::make('country')
                                            ->label('Страна'),
                                    ]),

                                // Телефон отдельно, если есть
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('Телефон')
                                    ->visible(fn ($record) => !empty($record->phone))
                                    ->copyable()
                                    ->columnSpanFull(),
                            ])
                            ->compact(),
                    ])
                    ->columnSpanFull(),
            ])
            ->visible(fn ($record) => $record->clientAddresses && $record->clientAddresses->count() > 0)
            ->collapsible();
    }
}
