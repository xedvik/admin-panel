<?php

namespace App\Services\Tables;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PromotionTableElementsFactory
{
    /**
     * Создать колонки таблицы
     */
    public function createTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Название')
                ->searchable()
                ->sortable(),

            TextColumn::make('description')
                ->label('Описание')
                ->limit(50)
                ->searchable(),

            TextColumn::make('start_date')
                ->label('Дата начала')
                ->date()
                ->sortable(),

            TextColumn::make('end_date')
                ->label('Дата окончания')
                ->date()
                ->sortable(),

            TextColumn::make('discount_type')
                ->label('Тип скидки')
                ->formatStateUsing(fn (string $state): string => match($state) {
                    'percentage' => 'Процентная',
                    'fixed' => 'Фиксированная',
                    default => $state,
                })
                ->sortable(),

            TextColumn::make('discount_value')
                ->label('Скидка')
                ->formatStateUsing(function ($record) {
                    return $record->discount_type === 'percentage'
                        ? "{$record->discount_value}%"
                        : "{$record->discount_value}₽";
                })
                ->sortable(),

            IconColumn::make('is_active')
                ->label('Активна')
                ->boolean()
                ->sortable(),

            TextColumn::make('created_at')
                ->label('Создана')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updated_at')
                ->label('Обновлена')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Создать фильтры таблицы
     */
    public function createTableFilters(): array
    {
        return [
            Filter::make('active')
                ->label('Только активные')
                ->query(fn (Builder $query): Builder => $query->where('is_active', true)),

            Filter::make('current')
                ->label('Текущие')
                ->query(fn (Builder $query): Builder => $query
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())),

            SelectFilter::make('discount_type')
                ->label('Тип скидки')
                ->options([
                    'percentage' => 'Процентная',
                    'fixed' => 'Фиксированная',
                ]),
        ];
    }

    /**
     * Создать действия для строк
     */
    public function createRowActions(): array
    {
        return [
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ];
    }

    /**
     * Создать массовые действия
     */
    public function createBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }
}
