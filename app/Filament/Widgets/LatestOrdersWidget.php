<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Contracts\Repositories\OrderRepositoryInterface;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Последние заказы';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $orderRepository = app(OrderRepositoryInterface::class);

        return $table
            ->query(
                $orderRepository->getQuery()
                    ->with(['client'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№ заказа')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->description(fn (Order $record): string => $record->client->email ?? '')
                    ->searchable(['client.first_name', 'client.last_name'])
                    ->sortable(),

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

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('total_items')
                    ->label('Товаров')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Просмотр')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Нет заказов')
            ->emptyStateDescription('Заказы появятся здесь после их создания.')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->striped();
    }
}
