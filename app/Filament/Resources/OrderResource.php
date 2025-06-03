<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Client;
use App\Contracts\Repositories\OrderRepositoryInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

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
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('Информация о заказе')
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->label('Номер заказа')
                                    ->required()
                                    ->maxLength(255)
                                    ->default(fn () => 'ORD-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT))
                                    ->unique('orders', 'order_number', ignoreRecord: true),

                                Forms\Components\Select::make('client_id')
                                    ->label('Клиент')
                                    ->relationship('client', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn (Client $record) => "{$record->full_name} ({$record->email})")
                                    ->searchable(['first_name', 'last_name', 'email'])
                                    ->preload()
                                    ->required(),

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
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(2),

                        Forms\Components\Section::make('Даты')
                            ->schema([
                                Forms\Components\DateTimePicker::make('shipped_at')
                                    ->label('Дата отправки'),

                                Forms\Components\DateTimePicker::make('delivered_at')
                                    ->label('Дата доставки'),
                            ])
                            ->columnSpan(1),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
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
                            ])
                            ->columnSpan(1),

                        Forms\Components\Section::make('Адреса')
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Fieldset::make('Адрес доставки')
                                            ->schema([
                                                Forms\Components\TextInput::make('shipping_address.first_name')
                                                    ->label('Имя')
                                                    ->required(),
                                                Forms\Components\TextInput::make('shipping_address.last_name')
                                                    ->label('Фамилия')
                                                    ->required(),
                                                Forms\Components\TextInput::make('shipping_address.company')
                                                    ->label('Компания'),
                                                Forms\Components\TextInput::make('shipping_address.street')
                                                    ->label('Адрес')
                                                    ->required(),
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('shipping_address.city')
                                                            ->label('Город')
                                                            ->required(),
                                                        Forms\Components\TextInput::make('shipping_address.postal_code')
                                                            ->label('Индекс'),
                                                    ]),
                                                Forms\Components\TextInput::make('shipping_address.phone')
                                                    ->label('Телефон')
                                                    ->tel(),
                                            ]),

                                        Forms\Components\Section::make('Адрес оплаты')
                                            ->schema([
                                                Forms\Components\TextInput::make('billing_address.first_name')
                                                    ->label('Имя'),
                                                Forms\Components\TextInput::make('billing_address.last_name')
                                                    ->label('Фамилия'),
                                                Forms\Components\TextInput::make('billing_address.company')
                                                    ->label('Компания'),
                                                Forms\Components\TextInput::make('billing_address.street')
                                                    ->label('Адрес'),
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('billing_address.city')
                                                            ->label('Город'),
                                                        Forms\Components\TextInput::make('billing_address.postal_code')
                                                            ->label('Индекс'),
                                                    ]),
                                                Forms\Components\TextInput::make('billing_address.phone')
                                                    ->label('Телефон')
                                                    ->tel(),
                                            ])
                                            ->collapsed(),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['client', 'orderItems']))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№ заказа')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('client_name')
                    ->label('Клиент')
                    ->searchable(['client.first_name', 'client.last_name'])
                    ->sortable()
                    ->getStateUsing(function (Order $record): string {
                        $orderRepository = app(OrderRepositoryInterface::class);
                        return $orderRepository->getOrderClientName($record->id);
                    })
                    ->description(fn (Order $record): string => $record->client->email ?? ''),

                Tables\Columns\TextColumn::make('status_label')
                    ->label('Статус')
                    ->badge()
                    ->getStateUsing(function (Order $record): string {
                        $orderRepository = app(OrderRepositoryInterface::class);
                        return $orderRepository->getOrderStatusLabel($record->id);
                    })
                    ->color(function (Order $record): string {
                        $orderRepository = app(OrderRepositoryInterface::class);
                        return $orderRepository->getOrderStatusColor($record->id);
                    }),

                Tables\Columns\TextColumn::make('payment_status_label')
                    ->label('Оплата')
                    ->badge()
                    ->getStateUsing(function (Order $record): string {
                        $orderRepository = app(OrderRepositoryInterface::class);
                        return $orderRepository->getOrderPaymentStatusLabel($record->id);
                    })
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
                    ->color('info')
                    ->getStateUsing(function (Order $record): int {
                        $orderRepository = app(OrderRepositoryInterface::class);
                        return $orderRepository->getOrderTotalItems($record->id);
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable()
                    ->weight('bold')
                    ->getStateUsing(fn (Order $record): int => $record->total_amount),

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
                    ->color('gray'),

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
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['value'])) {
                            $orderRepository = app(OrderRepositoryInterface::class);
                            $orderIds = $orderRepository->getByStatus($data['value'])->pluck('id');
                            return $query->whereIn('id', $orderIds);
                        }
                        return $query;
                    })
                    ->native(false),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Статус оплаты')
                    ->options([
                        'pending' => 'Ожидает оплаты',
                        'paid' => 'Оплачен',
                        'failed' => 'Ошибка оплаты',
                        'refunded' => 'Возврат',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['value'])) {
                            $orderRepository = app(OrderRepositoryInterface::class);
                            $orderIds = $orderRepository->getByPaymentStatus($data['value'])->pluck('id');
                            return $query->whereIn('id', $orderIds);
                        }
                        return $query;
                    })
                    ->native(false),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Способ оплаты')
                    ->options([
                        'card' => 'Банковская карта',
                        'cash' => 'Наличные',
                        'bank_transfer' => 'Банковский перевод',
                        'qr_code' => 'QR-код',
                    ])
                    ->native(false),

                Tables\Filters\Filter::make('created_at')
                    ->label('Дата создания')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('С даты'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('По дату'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Просмотр'),

                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),

                Tables\Actions\Action::make('markAsShipped')
                    ->label('Отметить как отправленный')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => in_array($record->status, ['pending', 'processing']))
                    ->action(function (Order $record): void {
                        $orderRepository = app(OrderRepositoryInterface::class);
                        $orderRepository->markAsShipped($record->id);

                        Notification::make()
                            ->title('Заказ отмечен как отправленный')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('markAsDelivered')
                    ->label('Отметить как доставленный')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->status === 'shipped')
                    ->action(function (Order $record): void {
                        $orderRepository = app(OrderRepositoryInterface::class);
                        $orderRepository->markAsDelivered($record->id);

                        Notification::make()
                            ->title('Заказ отмечен как доставленный')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('cancelOrder')
                    ->label('Отменить заказ')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => in_array($record->status, ['pending', 'processing']))
                    ->action(function (Order $record): void {
                        $orderRepository = app(OrderRepositoryInterface::class);
                        $success = $orderRepository->cancelOrder($record->id);

                        if ($success) {
                            Notification::make()
                                ->title('Заказ отменен')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Не удалось отменить заказ')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить'),

                    Tables\Actions\BulkAction::make('markAsShipped')
                        ->label('Отметить как отправленные')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $orderRepository = app(OrderRepositoryInterface::class);
                            $count = 0;

                            foreach ($records as $record) {
                                if (in_array($record->status, ['pending', 'processing'])) {
                                    $orderRepository->markAsShipped($record->id);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title("Отмечено как отправленные: {$count} заказов")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('markAsDelivered')
                        ->label('Отметить как доставленные')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $orderRepository = app(OrderRepositoryInterface::class);
                            $count = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'shipped') {
                                    $orderRepository->markAsDelivered($record->id);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title("Отмечено как доставленные: {$count} заказов")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('cancelOrders')
                        ->label('Отменить заказы')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $orderRepository = app(OrderRepositoryInterface::class);
                            $count = 0;

                            foreach ($records as $record) {
                                if (in_array($record->status, ['pending', 'processing'])) {
                                    $orderRepository->cancelOrder($record->id);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title("Отменено заказов: {$count}")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
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
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
