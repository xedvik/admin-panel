<?php

namespace App\Services\Forms;

use Filament\Forms;

class OrderForClientFormFieldFactory
{
    /**
     * Создать полную схему формы заказа для клиента
     */
    public function createFullFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)
                ->schema([
                    $this->createOrderInfoSection(),
                    $this->createAmountsSection(),
                ]),
        ];
    }

    /**
     * Создать секцию информации о заказе
     */
    public function createOrderInfoSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Информация о заказе')
            ->schema([
                $this->createOrderNumberField(),
                $this->createStatusField(),
                $this->createPaymentStatusField(),
                $this->createPaymentMethodField(),
                $this->createCurrencyField(),
                $this->createNotesField(),
            ])
            ->columnSpan(1);
    }

    /**
     * Создать секцию сумм
     */
    public function createAmountsSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Суммы')
            ->schema([
                $this->createSubtotalField(),
                $this->createTaxAmountField(),
                $this->createShippingAmountField(),
                $this->createDiscountAmountField(),
                $this->createTotalAmountField(),
                $this->createShippedAtField(),
                $this->createDeliveredAtField(),
            ])
            ->columnSpan(1);
    }

    /**
     * Создать поле номера заказа
     */
    private function createOrderNumberField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('order_number')
            ->label('Номер заказа')
            ->required()
            ->maxLength(255)
            ->default(fn () => 'ORD-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT))
            ->unique('orders', 'order_number', ignoreRecord: true);
    }

    /**
     * Создать поле статуса заказа
     */
    private function createStatusField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('status')
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
            ->native(false);
    }

    /**
     * Создать поле статуса оплаты
     */
    private function createPaymentStatusField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('payment_status')
            ->label('Статус оплаты')
            ->options([
                'pending' => 'Ожидает оплаты',
                'paid' => 'Оплачен',
                'failed' => 'Ошибка оплаты',
                'refunded' => 'Возврат',
            ])
            ->required()
            ->default('pending')
            ->native(false);
    }

    /**
     * Создать поле способа оплаты
     */
    private function createPaymentMethodField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('payment_method')
            ->label('Способ оплаты')
            ->options([
                'card' => 'Банковская карта',
                'cash' => 'Наличные',
                'bank_transfer' => 'Банковский перевод',
                'qr_code' => 'QR-код',
            ])
            ->native(false);
    }

    /**
     * Создать поле валюты
     */
    private function createCurrencyField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('currency')
            ->label('Валюта')
            ->default('RUB')
            ->required()
            ->maxLength(3);
    }

    /**
     * Создать поле примечаний
     */
    private function createNotesField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('notes')
            ->label('Примечания')
            ->rows(3);
    }

    /**
     * Создать поле суммы без скидок
     */
    private function createSubtotalField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('subtotal')
            ->label('Сумма без скидок')
            ->numeric()
            ->required()
            ->prefix('₽');
    }

    /**
     * Создать поле суммы налога
     */
    private function createTaxAmountField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('tax_amount')
            ->label('Сумма налога')
            ->numeric()
            ->prefix('₽')
            ->default(0);
    }

    /**
     * Создать поле стоимости доставки
     */
    private function createShippingAmountField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('shipping_amount')
            ->label('Стоимость доставки')
            ->numeric()
            ->prefix('₽')
            ->default(0);
    }

    /**
     * Создать поле размера скидки
     */
    private function createDiscountAmountField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('discount_amount')
            ->label('Размер скидки')
            ->numeric()
            ->prefix('₽')
            ->default(0);
    }

    /**
     * Создать поле итоговой суммы
     */
    private function createTotalAmountField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('total_amount')
            ->label('Итоговая сумма')
            ->numeric()
            ->required()
            ->prefix('₽');
    }

    /**
     * Создать поле даты отправки
     */
    private function createShippedAtField(): Forms\Components\DateTimePicker
    {
        return Forms\Components\DateTimePicker::make('shipped_at')
            ->label('Дата отправки');
    }

    /**
     * Создать поле даты доставки
     */
    private function createDeliveredAtField(): Forms\Components\DateTimePicker
    {
        return Forms\Components\DateTimePicker::make('delivered_at')
            ->label('Дата доставки');
    }
}
