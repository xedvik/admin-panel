<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Order;
use App\Contracts\Repositories\OrderRepositoryInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Заказы клиента';

    protected static ?string $modelLabel = 'Заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make('Информация о заказе')
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->label('Номер заказа')
                                    ->required()
                                    ->maxLength(255)
                                    ->default(fn () => 'ORD-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT))
                                    ->unique('orders', 'order_number', ignoreRecord: true),

                                Forms\Components\Select::make('status')
                                    ->label('Статус заказа')
                                    ->options([
                                        'pending' => 'В ожидании',
                                        'processing' => 'В обработке',
                                        'shipped' => 'Отправлен',
                                        'delivered' => 'Доставлен',
                                        'cancelled' => 'Отменен',
                                    ])
                                    ->required()
                                    ->default('pending')
                                    ->native(false),

                                Forms\Components\Select::make('payment_status')
                                    ->label('Статус оплаты')
                                    ->options([
                                        'pending' => 'Ожидает оплаты',
                                        'paid' => 'Оплачен',
                                        'failed' => 'Ошибка оплаты',
                                        'refunded' => 'Возврат',
                                    ])
                                    ->required()
                                    ->default('pending')
                                    ->native(false),

                                Forms\Components\Select::make('payment_method')
                                    ->label('Способ оплаты')
                                    ->options([
                                        'card' => 'Банковская карта',
                                        'cash' => 'Наличные',
                                        'bank_transfer' => 'Банковский перевод',
                                        'qr_code' => 'QR-код',
                                    ])
                                    ->native(false),

                                Forms\Components\TextInput::make('currency')
                                    ->label('Валюта')
                                    ->default('RUB')
                                    ->required()
                                    ->maxLength(3),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Примечания')
                                    ->rows(3),
                            ])
                            ->columnSpan(1),

                        Forms\Components\Section::make('Суммы')
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Сумма без скидок')
                                    ->numeric()
                                    ->required()
                                    ->prefix('₽'),

                                Forms\Components\TextInput::make('tax_amount')
                                    ->label('Сумма налога')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->default(0),

                                Forms\Components\TextInput::make('shipping_amount')
                                    ->label('Стоимость доставки')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->default(0),

                                Forms\Components\TextInput::make('discount_amount')
                                    ->label('Размер скидки')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->default(0),

                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Итоговая сумма')
                                    ->numeric()
                                    ->required()
                                    ->prefix('₽'),

                                Forms\Components\DateTimePicker::make('shipped_at')
                                    ->label('Дата отправки'),

                                Forms\Components\DateTimePicker::make('delivered_at')
                                    ->label('Дата доставки'),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№ заказа')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status_label')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (Order $record): string => $record->status_color),

                Tables\Columns\TextColumn::make('payment_status_label')
                    ->label('Оплата')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Оплачен' => 'success',
                        'Ожидает оплаты' => 'warning',
                        'Ошибка оплаты' => 'danger',
                        'Возврат' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_items')
                    ->label('Товаров')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Способ оплаты')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'card' => 'Карта',
                        'cash' => 'Наличные',
                        'bank_transfer' => 'Перевод',
                        'qr_code' => 'QR-код',
                        default => $state,
                    })
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('shipped_at')
                    ->label('Отправлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус заказа')
                    ->options([
                        'pending' => 'В ожидании',
                        'processing' => 'В обработке',
                        'shipped' => 'Отправлен',
                        'delivered' => 'Доставлен',
                        'cancelled' => 'Отменен',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Статус оплаты')
                    ->options([
                        'pending' => 'Ожидает оплаты',
                        'paid' => 'Оплачен',
                        'failed' => 'Ошибка оплаты',
                        'refunded' => 'Возврат',
                    ])
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Создать заказ'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Просмотр')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record)),
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
