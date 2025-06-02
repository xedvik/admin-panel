<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Клиенты';

    protected static ?string $modelLabel = 'Клиент';

    protected static ?string $pluralModelLabel = 'Клиенты';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make('Личная информация')
                            ->schema([
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
                                    ->unique(Client::class, 'email', ignoreRecord: true)
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
                            ])
                            ->columnSpan(1),

                        Forms\Components\Section::make('Настройки')
                            ->schema([
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
                            ])
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Адреса')
                    ->schema([
                        Forms\Components\Repeater::make('addresses')
                            ->label('Адреса клиента')
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

                                        Forms\Components\Toggle::make('is_default')
                                            ->label('Основной адрес')
                                            ->default(false),

                                        Forms\Components\TextInput::make('company')
                                            ->label('Компания'),
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
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Имя Фамилия')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->description(fn (Client $record): string => $record->email),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('customer_status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'VIP' => 'success',
                        'Постоянный' => 'warning',
                        'Обычный' => 'gray',
                        'Новый' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Заказов')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Потрачено')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email подтвержден')
                    ->boolean()
                    ->getStateUsing(fn (Client $record) => !is_null($record->email_verified_at))
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),

                Tables\Columns\IconColumn::make('accepts_marketing')
                    ->label('Маркетинг')
                    ->boolean()
                    ->trueIcon('heroicon-o-megaphone')
                    ->falseIcon('heroicon-o-no-symbol'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Регистрация')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные')
                    ->boolean()
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('accepts_marketing')
                    ->label('Согласие на маркетинг')
                    ->boolean()
                    ->trueLabel('Согласны')
                    ->falseLabel('Не согласны')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email подтвержден')
                    ->boolean()
                    ->trueLabel('Подтвержден')
                    ->falseLabel('Не подтвержден')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                    )
                    ->native(false),

                Tables\Filters\SelectFilter::make('customer_status')
                    ->label('Статус клиента')
                    ->options([
                        'Новый' => 'Новый',
                        'Обычный' => 'Обычный',
                        'Постоянный' => 'Постоянный',
                        'VIP' => 'VIP',
                    ])
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Просмотр'),
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
