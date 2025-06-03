<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use App\Contracts\Repositories\SettingRepositoryInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Настройки';

    protected static ?string $modelLabel = 'Настройка';

    protected static ?string $pluralModelLabel = 'Настройки';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make('Основная информация')
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('Ключ')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('settings', 'key', ignoreRecord: true)
                                    ->rules(['alpha_dash'])
                                    ->helperText('Уникальный ключ настройки'),

                                Forms\Components\TextInput::make('label')
                                    ->label('Название')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('description')
                                    ->label('Описание')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('type')
                                    ->label('Тип данных')
                                    ->options([
                                        'string' => 'Строка',
                                        'integer' => 'Число',
                                        'boolean' => 'Да/Нет',
                                        'json' => 'JSON',
                                        'float' => 'Дробное число',
                                    ])
                                    ->required()
                                    ->default('string')
                                    ->live()
                                    ->native(false),

                                Forms\Components\TextInput::make('group')
                                    ->label('Группа')
                                    ->maxLength(255)
                                    ->helperText('Для группировки настроек'),
                            ])
                            ->columnSpan(1),

                        Forms\Components\Section::make('Значение и настройки')
                            ->schema([
                                Forms\Components\Toggle::make('is_public')
                                    ->label('Публичная настройка')
                                    ->default(false)
                                    ->helperText('Доступна для фронтенда'),

                                Forms\Components\TextInput::make('value')
                                    ->label('Значение')
                                    ->required()
                                    ->maxLength(5000)
                                    ->visible(fn (Forms\Get $get) => !in_array($get('type'), ['boolean', 'json']))
                                    ->columnSpanFull(),

                                Forms\Components\Toggle::make('value')
                                    ->label('Значение')
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'boolean')
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('value')
                                    ->label('Значение JSON')
                                    ->rows(5)
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'json')
                                    ->helperText('Введите валидный JSON')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Ключ')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('label')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string' => 'gray',
                        'integer' => 'info',
                        'boolean' => 'warning',
                        'json' => 'success',
                        'float' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('group')
                    ->label('Группа')
                    ->badge()
                    ->color('info')
                    ->default('—'),

                Tables\Columns\TextColumn::make('value')
                    ->label('Значение')
                    ->limit(50)
                    ->formatStateUsing(function ($state, Setting $record) {
                        if ($record->type === 'boolean') {
                            return $state ? 'Да' : 'Нет';
                        }
                        if ($record->type === 'json') {
                            return 'JSON объект';
                        }
                        return $state;
                    }),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Публичная')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип данных')
                    ->options([
                        'string' => 'Строка',
                        'integer' => 'Число',
                        'boolean' => 'Да/Нет',
                        'json' => 'JSON',
                        'float' => 'Дробное число',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('group')
                    ->label('Группа')
                    ->options(function () {
                        $settingRepository = app(SettingRepositoryInterface::class);
                        return $settingRepository->getGroups();
                    })
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Публичные')
                    ->boolean()
                    ->trueLabel('Только публичные')
                    ->falseLabel('Только приватные')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),

                Tables\Actions\Action::make('clearCache')
                    ->label('Очистить кеш')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (): void {
                        $settingRepository = app(SettingRepositoryInterface::class);
                        $settingRepository->clearCache();

                        Notification::make()
                            ->title('Кеш настроек очищен')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить'),

                    Tables\Actions\BulkAction::make('makePublic')
                        ->label('Сделать публичными')
                        ->icon('heroicon-o-globe-alt')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $settingRepository = app(SettingRepositoryInterface::class);
                            $count = 0;

                            foreach ($records as $record) {
                                $settingRepository->setValue($record->key, $record->value, $record->type, ['is_public' => true]);
                                $count++;
                            }

                            Notification::make()
                                ->title("Сделано публичными: {$count} настроек")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('makePrivate')
                        ->label('Сделать приватными')
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $settingRepository = app(SettingRepositoryInterface::class);
                            $count = 0;

                            foreach ($records as $record) {
                                $settingRepository->setValue($record->key, $record->value, $record->type, ['is_public' => false]);
                                $count++;
                            }

                            Notification::make()
                                ->title("Сделано приватными: {$count} настроек")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('clearCache')
                        ->label('Очистить кеш')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (): void {
                            $settingRepository = app(SettingRepositoryInterface::class);
                            $settingRepository->clearCache();

                            Notification::make()
                                ->title('Кеш настроек очищен')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('key');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
