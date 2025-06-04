<?php

namespace App\Services\Tables;

use App\Models\Order;
use Filament\Tables;

class OrderForClientTableElementsFactory
{
    /**
     * Создать все колонки таблицы заказов клиента
     */
    public function createTableColumns(): array
    {
        return [
            $this->createOrderNumberColumn(),
            $this->createStatusLabelColumn(),
            $this->createPaymentStatusLabelColumn(),
            $this->createTotalItemsColumn(),
            $this->createTotalAmountColumn(),
            $this->createPaymentMethodColumn(),
            $this->createCreatedAtColumn(),
            $this->createShippedAtColumn(),
        ];
    }

    /**
     * Создать все фильтры таблицы
     */
    public function createTableFilters(): array
    {
        return [
            $this->createStatusFilter(),
            $this->createPaymentStatusFilter(),
        ];
    }

    /**
     * Создать действия заголовка таблицы
     */
    public function createHeaderActions(): array
    {
        return [
            Tables\Actions\CreateAction::make()
                ->label('Создать заказ'),
        ];
    }

    /**
     * Создать действия строк таблицы
     */
    public function createRowActions(): array
    {
        return [
            Tables\Actions\ViewAction::make()
                ->label('Просмотр')
                ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record)),
            Tables\Actions\EditAction::make()
                ->label('Редактировать'),
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
                    ->label('Удалить выбранные'),
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
     * Создать колонку статуса заказа
     */
    private function createStatusLabelColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('status_label')
            ->label('Статус')
            ->badge()
            ->color(fn (Order $record): string => $record->status_color);
    }

    /**
     * Создать колонку статуса оплаты
     */
    private function createPaymentStatusLabelColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('payment_status_label')
            ->label('Оплата')
            ->badge()
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
            ->color('info');
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
            ->weight('bold');
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
            ->color('gray')
            ->toggleable(isToggledHiddenByDefault: true);
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
            ->options([
                'pending' => 'В ожидании',
                'processing' => 'В обработке',
                'shipped' => 'Отправлен',
                'delivered' => 'Доставлен',
                'cancelled' => 'Отменен',
            ])
            ->native(false);
    }

    /**
     * Создать фильтр по статусу оплаты
     */
    private function createPaymentStatusFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('payment_status')
            ->label('Статус оплаты')
            ->options([
                'pending' => 'Ожидает оплаты',
                'paid' => 'Оплачен',
                'failed' => 'Ошибка оплаты',
                'refunded' => 'Возврат',
            ])
            ->native(false);
    }
}
