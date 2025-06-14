<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientStatusResource\Pages;
use App\Models\ClientStatus;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class ClientStatusResource extends Resource
{
    protected static ?string $model = ClientStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';
    protected static ?string $navigationLabel = 'Статусы клиентов';
    protected static ?string $modelLabel = 'Статус клиента';
    protected static ?string $pluralModelLabel = 'Статусы клиентов';
    protected static ?string $navigationGroup = 'Клиенты';
    protected static ?int $navigationSort = 1;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Код статуса')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('label')
                ->label('Название')
                ->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('name')->label('Код статуса')->sortable(),
            Tables\Columns\TextColumn::make('label')->label('Название')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->label('Создан')->dateTime('d.m.Y H:i')->sortable(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientStatuses::route('/'),
            'create' => Pages\CreateClientStatus::route('/create'),
            'edit' => Pages\EditClientStatus::route('/{record}/edit'),
        ];
    }
}
