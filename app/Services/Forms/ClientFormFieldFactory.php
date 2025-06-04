<?php

namespace App\Services\Forms;

use Filament\Forms;

class ClientFormFieldFactory
{
    /**
     * Создать поля личной информации клиента
     */
    public function createPersonalInfoFields(): array
    {
        return [
            Forms\Components\TextInput::make('first_name')
                ->label('Имя')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('last_name')
                ->label('Фамилия')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique('clients', 'email', ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\TextInput::make('phone')
                ->label('Телефон')
                ->tel()
                ->maxLength(255),

            Forms\Components\DatePicker::make('date_of_birth')
                ->label('Дата рождения')
                ->maxDate(now()->subYears(10)),

            Forms\Components\Select::make('gender')
                ->label('Пол')
                ->options([
                    'male' => 'Мужской',
                    'female' => 'Женский',
                ])
                ->native(false),

            Forms\Components\TextInput::make('company')
                ->label('Компания')
                ->columnSpanFull(),
        ];
    }

    /**
     * Создать поля настроек клиента
     */
    public function createSettingsFields(): array
    {
        return [
            Forms\Components\Toggle::make('is_active')
                ->label('Активен')
                ->default(true)
                ->helperText('Может ли клиент заходить в личный кабинет'),

            Forms\Components\Toggle::make('accepts_marketing')
                ->label('Согласие на маркетинг')
                ->default(false)
                ->helperText('Согласие на получение рекламных материалов'),

            Forms\Components\DateTimePicker::make('email_verified_at')
                ->label('Email подтвержден')
                ->helperText('Дата подтверждения email адреса'),
        ];
    }

    /**
     * Создать секцию адресов клиента
     */
    public function createAddressesSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Адреса')
            ->schema([
                Forms\Components\Repeater::make('clientAddresses')
                    ->label('Адреса клиента')
                    ->relationship('clientAddresses')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Тип адреса')
                                    ->options([
                                        'shipping' => 'Доставка',
                                        'billing' => 'Оплата',
                                    ])
                                    ->required()
                                    ->default('shipping'),

                                Forms\Components\TextInput::make('label')
                                    ->label('Название')
                                    ->placeholder('Дом, Офис, Дача...')
                                    ->required(),

                                Forms\Components\Toggle::make('is_default')
                                    ->label('Основной адрес')
                                    ->default(false),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('Имя')
                                    ->required(),

                                Forms\Components\TextInput::make('last_name')
                                    ->label('Фамилия')
                                    ->required(),
                            ]),

                        Forms\Components\TextInput::make('company')
                            ->label('Компания')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('street')
                            ->label('Адрес')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('city')
                                    ->label('Город')
                                    ->required(),

                                Forms\Components\TextInput::make('state')
                                    ->label('Область/Регион'),

                                Forms\Components\TextInput::make('postal_code')
                                    ->label('Почтовый индекс'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('country')
                                    ->label('Страна')
                                    ->default('Russia'),

                                Forms\Components\TextInput::make('phone')
                                    ->label('Телефон')
                                    ->tel(),
                            ]),
                    ])
                    ->addActionLabel('Добавить адрес')
                    ->collapsible()
                    ->cloneable()
                    ->columnSpanFull(),
            ])
            ->collapsed();
    }

    /**
     * Создать полную схему формы клиента
     */
    public function createFullFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Section::make('Личная информация')
                        ->schema($this->createPersonalInfoFields())
                        ->columnSpan(1),

                    Forms\Components\Section::make('Настройки')
                        ->schema($this->createSettingsFields())
                        ->columnSpan(1),
                ]),

            $this->createAddressesSection(),
        ];
    }
}
