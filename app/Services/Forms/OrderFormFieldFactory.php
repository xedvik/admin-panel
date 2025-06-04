<?php

namespace App\Services\Forms;

use App\Models\Client;
use App\Services\Business\ClientAddressService;
use Filament\Forms;

class OrderFormFieldFactory
{
    public function __construct(
        private ClientAddressService $clientAddressService
    ) {}

    /**
     * Создать основные поля заказа
     */
    public function createOrderInfoFields(): array
    {
        return [
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

            $this->createStatusField(),
            $this->createPaymentStatusField(),
            $this->createPaymentMethodField(),

            Forms\Components\TextInput::make('currency')
                ->label('Валюта')
                ->default('RUB')
                ->required()
                ->maxLength(3),

            Forms\Components\Textarea::make('notes')
                ->label('Примечания')
                ->rows(3)
                ->columnSpanFull(),
        ];
    }

    /**
     * Создать поля дат
     */
    public function createDateFields(): array
    {
        return [
            Forms\Components\DateTimePicker::make('shipped_at')
                ->label('Дата отправки'),

            Forms\Components\DateTimePicker::make('delivered_at')
                ->label('Дата доставки'),
        ];
    }

    /**
     * Создать поля сумм
     */
    public function createAmountFields(): array
    {
        return [
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
        ];
    }

    /**
     * Создать поля адресов
     */
    public function createAddressFields(): array
    {
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Fieldset::make('Адрес доставки')
                        ->schema($this->createShippingAddressFields()),

                    Forms\Components\Fieldset::make('Адрес выставления счета')
                        ->schema($this->createBillingAddressFields()),
                ]),
        ];
    }

    /**
     * Создать основной макет формы
     */
    public function createMainLayout(): array
    {
        return [
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Section::make('Информация о заказе')
                        ->schema($this->createOrderInfoFields())
                        ->columnSpan(2),

                    Forms\Components\Section::make('Даты')
                        ->schema($this->createDateFields())
                        ->columnSpan(1),
                ]),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Section::make('Суммы')
                        ->schema($this->createAmountFields())
                        ->columnSpan(1),

                    Forms\Components\Section::make('Адреса')
                        ->schema($this->createAddressFields())
                        ->columnSpan(1),
                ]),
        ];
    }

    /**
     * Получить опции статусов заказа
     */
    public function getOrderStatusOptions(): array
    {
        return [
            'pending' => 'В ожидании',
            'processing' => 'В обработке',
            'shipped' => 'Отправлен',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменен',
        ];
    }

    /**
     * Получить опции статусов оплаты
     */
    public function getPaymentStatusOptions(): array
    {
        return [
            'pending' => 'Ожидает оплаты',
            'paid' => 'Оплачен',
            'failed' => 'Ошибка оплаты',
            'refunded' => 'Возврат',
        ];
    }

    /**
     * Получить опции способов оплаты
     */
    public function getPaymentMethodOptions(): array
    {
        return [
            'card' => 'Банковская карта',
            'cash' => 'Наличные',
            'bank_transfer' => 'Банковский перевод',
            'qr_code' => 'QR-код',
        ];
    }

    /**
     * Создать поле статуса заказа
     */
    private function createStatusField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('status')
            ->label('Статус заказа')
            ->options($this->getOrderStatusOptions())
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
            ->options($this->getPaymentStatusOptions())
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
            ->options($this->getPaymentMethodOptions())
            ->native(false);
    }

    /**
     * Создать поля адреса доставки
     */
    private function createShippingAddressFields(): array
    {
        return [
            Forms\Components\Select::make('client_shipping_address')
                ->label('Выбрать из сохраненных адресов')
                ->placeholder('Выберите адрес или введите новый')
                ->options(function (Forms\Get $get) {
                    $clientId = $get('client_id');
                    if (!$clientId) return [];

                    $client = Client::find($clientId);
                    if (!$client || !$client->addresses) return [];

                    return $this->clientAddressService->getAddressOptionsForSelect($client->addresses, 'shipping');
                })
                ->reactive()
                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                    if ($state !== null) {
                        $this->fillAddressFromClient($state, $get, $set, 'shipping_address');
                    }
                })
                ->native(false),

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
                        ->label('Индекс')
                        ->required(),
                ]),
            Forms\Components\TextInput::make('shipping_address.phone')
                ->label('Телефон')
                ->tel(),
        ];
    }

    /**
     * Создать поля адреса выставления счета
     */
    private function createBillingAddressFields(): array
    {
        return [
            Forms\Components\Checkbox::make('same_as_shipping')
                ->label('Такой же как адрес доставки')
                ->reactive()
                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                    if ($state) {
                        $this->copyShippingToBilling($get, $set);
                    }
                }),

            Forms\Components\TextInput::make('billing_address.first_name')
                ->label('Имя')
                ->required()
                ->hidden(fn (Forms\Get $get) => $get('same_as_shipping')),
            Forms\Components\TextInput::make('billing_address.last_name')
                ->label('Фамилия')
                ->required()
                ->hidden(fn (Forms\Get $get) => $get('same_as_shipping')),
            Forms\Components\TextInput::make('billing_address.company')
                ->label('Компания')
                ->hidden(fn (Forms\Get $get) => $get('same_as_shipping')),
            Forms\Components\TextInput::make('billing_address.street')
                ->label('Адрес')
                ->required()
                ->hidden(fn (Forms\Get $get) => $get('same_as_shipping')),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('billing_address.city')
                        ->label('Город')
                        ->required()
                        ->hidden(fn (Forms\Get $get) => $get('same_as_shipping')),
                    Forms\Components\TextInput::make('billing_address.postal_code')
                        ->label('Индекс')
                        ->required()
                        ->hidden(fn (Forms\Get $get) => $get('same_as_shipping')),
                ])
                ->hidden(fn (Forms\Get $get) => $get('same_as_shipping')),
            Forms\Components\TextInput::make('billing_address.phone')
                ->label('Телефон')
                ->tel()
                ->hidden(fn (Forms\Get $get) => $get('same_as_shipping')),
        ];
    }

    /**
     * Заполнить адрес из клиента
     */
    private function fillAddressFromClient($state, Forms\Get $get, Forms\Set $set, string $prefix): void
    {
        $clientId = $get('client_id');
        $client = Client::find($clientId);
        if ($client && isset($client->addresses[$state])) {
            $address = $client->addresses[$state];
            $set("{$prefix}.first_name", $address['first_name'] ?? '');
            $set("{$prefix}.last_name", $address['last_name'] ?? '');
            $set("{$prefix}.company", $address['company'] ?? '');
            $set("{$prefix}.street", $address['street'] ?? '');
            $set("{$prefix}.city", $address['city'] ?? '');
            $set("{$prefix}.postal_code", $address['postal_code'] ?? '');
            $set("{$prefix}.phone", $address['phone'] ?? '');
        }
    }

    /**
     * Копировать адрес доставки в адрес выставления счета
     */
    private function copyShippingToBilling(Forms\Get $get, Forms\Set $set): void
    {
        $set('billing_address.first_name', $get('shipping_address.first_name'));
        $set('billing_address.last_name', $get('shipping_address.last_name'));
        $set('billing_address.company', $get('shipping_address.company'));
        $set('billing_address.street', $get('shipping_address.street'));
        $set('billing_address.city', $get('shipping_address.city'));
        $set('billing_address.postal_code', $get('shipping_address.postal_code'));
        $set('billing_address.phone', $get('shipping_address.phone'));
    }
}
