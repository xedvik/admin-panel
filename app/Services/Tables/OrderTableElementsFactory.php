<?php

namespace App\Services\Tables;

use App\Models\Order;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Services\Forms\OrderFormFieldFactory;
use Filament\Tables;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class OrderTableElementsFactory
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private OrderFormFieldFactory $formFieldFactory
    ) {}

    /**
     * Создать колонки для таблицы заказов
     */
    public function createTableColumns(): array
    {
        return [
            $this->createOrderNumberColumn(),
            $this->createClientNameColumn(),
            $this->createStatusColumn(),
            $this->createPaymentStatusColumn(),
            $this->createTotalItemsColumn(),
            $this->createTotalAmountColumn(),
            $this->createPaymentMethodColumn(),
            $this->createCreatedAtColumn(),
            $this->createShippedAtColumn(),
        ];
    }

    /**
     * Создать фильтры для таблицы заказов
     */
    public function createTableFilters(): array
    {
        return [
            $this->createStatusFilter(),
            $this->createPaymentStatusFilter(),
            $this->createPaymentMethodFilter(),
            $this->createDateFilter(),
        ];
    }

    /**
     * Создать действия для строк таблицы
     */
    public function createRowActions(): array
    {
        return [
            Tables\Actions\ViewAction::make()
                ->label('Просмотр'),

            Tables\Actions\EditAction::make()
                ->label('Редактировать'),

            $this->createMarkAsShippedAction(),
            $this->createMarkAsDeliveredAction(),
            $this->createCancelOrderAction(),
        ];
    }

    /**
     * Создать массовые действия
     */
    public function createBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Удалить'),

                $this->createBulkMarkAsShippedAction(),
                $this->createBulkMarkAsDeliveredAction(),
                $this->createBulkCancelOrdersAction(),
            ]),
        ];
    }

    /**
     * Создать колонку номера заказа
     */
    private function createOrderNumberColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('order_number')
            ->label('№ заказа')
            ->searchable()
            ->sortable()
            ->copyable()
            ->weight('bold');
    }

    /**
     * Создать колонку имени клиента
     */
    private function createClientNameColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('client_name')
            ->label('Клиент')
            ->searchable(['client.first_name', 'client.last_name'])
            ->sortable()
            ->getStateUsing(function (Order $record): string {
                return $this->orderRepository->getOrderClientName($record->id);
            })
            ->description(fn (Order $record): string => $record->client->email ?? '');
    }

    /**
     * Создать колонку статуса заказа
     */
    private function createStatusColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('status_label')
            ->label('Статус')
            ->badge()
            ->getStateUsing(function (Order $record): string {
                return $this->orderRepository->getOrderStatusLabel($record->id);
            })
            ->color(function (Order $record): string {
                return $this->orderRepository->getOrderStatusColor($record->id);
            });
    }

    /**
     * Создать колонку статуса оплаты
     */
    private function createPaymentStatusColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('payment_status_label')
            ->label('Оплата')
            ->badge()
            ->getStateUsing(function (Order $record): string {
                return $this->orderRepository->getOrderPaymentStatusLabel($record->id);
            })
            ->color(fn (string $state): string => match ($state) {
                'Оплачен' => 'success',
                'Ожидает оплаты' => 'warning',
                'Ошибка оплаты' => 'danger',
                'Возврат' => 'gray',
                default => 'gray',
            });
    }

    /**
     * Создать колонку количества товаров
     */
    private function createTotalItemsColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('total_items')
            ->label('Товаров')
            ->badge()
            ->color('info')
            ->getStateUsing(function (Order $record): int {
                return $this->orderRepository->getOrderTotalItems($record->id);
            });
    }

    /**
     * Создать колонку общей суммы
     */
    private function createTotalAmountColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('total_amount')
            ->label('Сумма')
            ->money('RUB')
            ->sortable()
            ->weight('bold')
            ->getStateUsing(fn (Order $record): int => $record->total_amount);
    }

    /**
     * Создать колонку способа оплаты
     */
    private function createPaymentMethodColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('payment_method')
            ->label('Способ оплаты')
            ->formatStateUsing(fn (string $state): string => match ($state) {
                'card' => 'Карта',
                'cash' => 'Наличные',
                'bank_transfer' => 'Перевод',
                'qr_code' => 'QR-код',
                default => $state,
            })
            ->badge()
            ->color('gray');
    }

    /**
     * Создать колонку даты создания
     */
    private function createCreatedAtColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('created_at')
            ->label('Дата создания')
            ->dateTime('d.m.Y H:i')
            ->sortable();
    }

    /**
     * Создать колонку даты отправки
     */
    private function createShippedAtColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('shipped_at')
            ->label('Отправлен')
            ->dateTime('d.m.Y H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * Создать фильтр по статусу заказа
     */
    private function createStatusFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('status')
            ->label('Статус заказа')
            ->options($this->formFieldFactory->getOrderStatusOptions())
            ->query(function (Builder $query, array $data): Builder {
                if (filled($data['value'])) {
                    $orderIds = $this->orderRepository->getByStatus($data['value'])->pluck('id');
                    return $query->whereIn('id', $orderIds);
                }
                return $query;
            })
            ->native(false);
    }

    /**
     * Создать фильтр по статусу оплаты
     */
    private function createPaymentStatusFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('payment_status')
            ->label('Статус оплаты')
            ->options($this->formFieldFactory->getPaymentStatusOptions())
            ->query(function (Builder $query, array $data): Builder {
                if (filled($data['value'])) {
                    $orderIds = $this->orderRepository->getByPaymentStatus($data['value'])->pluck('id');
                    return $query->whereIn('id', $orderIds);
                }
                return $query;
            })
            ->native(false);
    }

    /**
     * Создать фильтр по способу оплаты
     */
    private function createPaymentMethodFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('payment_method')
            ->label('Способ оплаты')
            ->options($this->formFieldFactory->getPaymentMethodOptions())
            ->native(false);
    }

    /**
     * Создать фильтр по дате
     */
    private function createDateFilter(): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make('created_at')
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
            });
    }

    /**
     * Создать действие "Отметить как отправленный"
     */
    private function createMarkAsShippedAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('markAsShipped')
            ->label('Отметить как отправленный')
            ->icon('heroicon-o-truck')
            ->color('info')
            ->requiresConfirmation()
            ->visible(fn (Order $record): bool => in_array($record->status, ['pending', 'processing']))
            ->action(function (Order $record): void {
                $this->orderRepository->markAsShipped($record->id);

                Notification::make()
                    ->title('Заказ отмечен как отправленный')
                    ->success()
                    ->send();
            });
    }

    /**
     * Создать действие "Отметить как доставленный"
     */
    private function createMarkAsDeliveredAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('markAsDelivered')
            ->label('Отметить как доставленный')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Order $record): bool => $record->status === 'shipped')
            ->action(function (Order $record): void {
                $this->orderRepository->markAsDelivered($record->id);

                Notification::make()
                    ->title('Заказ отмечен как доставленный')
                    ->success()
                    ->send();
            });
    }

    /**
     * Создать действие "Отменить заказ"
     */
    private function createCancelOrderAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('cancelOrder')
            ->label('Отменить заказ')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Order $record): bool => in_array($record->status, ['pending', 'processing']))
            ->action(function (Order $record): void {
                $success = $this->orderRepository->cancelOrder($record->id);

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
            });
    }

    /**
     * Создать массовое действие "Отметить как отправленные"
     */
    private function createBulkMarkAsShippedAction(): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make('markAsShipped')
            ->label('Отметить как отправленные')
            ->icon('heroicon-o-truck')
            ->color('info')
            ->requiresConfirmation()
            ->action(function ($records): void {
                $count = 0;

                foreach ($records as $record) {
                    if (in_array($record->status, ['pending', 'processing'])) {
                        $this->orderRepository->markAsShipped($record->id);
                        $count++;
                    }
                }

                Notification::make()
                    ->title("Отмечено как отправленные: {$count} заказов")
                    ->success()
                    ->send();
            });
    }

    /**
     * Создать массовое действие "Отметить как доставленные"
     */
    private function createBulkMarkAsDeliveredAction(): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make('markAsDelivered')
            ->label('Отметить как доставленные')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->action(function ($records): void {
                $count = 0;

                foreach ($records as $record) {
                    if ($record->status === 'shipped') {
                        $this->orderRepository->markAsDelivered($record->id);
                        $count++;
                    }
                }

                Notification::make()
                    ->title("Отмечено как доставленные: {$count} заказов")
                    ->success()
                    ->send();
            });
    }

    /**
     * Создать массовое действие "Отменить заказы"
     */
    private function createBulkCancelOrdersAction(): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make('cancelOrders')
            ->label('Отменить заказы')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function ($records): void {
                $count = 0;

                foreach ($records as $record) {
                    if (in_array($record->status, ['pending', 'processing'])) {
                        $success = $this->orderRepository->cancelOrder($record->id);
                        if ($success) {
                            $count++;
                        }
                    }
                }

                Notification::make()
                    ->title("Отменено заказов: {$count}")
                    ->success()
                    ->send();
            });
    }
}
