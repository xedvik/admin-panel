<?php

namespace App\Services\Tables;

use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class ProductInCategoryTableElementsFactory
{
    /**
     * Создать все колонки таблицы товаров в категории
     */
    public function createTableColumns(): array
    {
        return [
            $this->createImageColumn(),
            $this->createNameColumn(),
            $this->createPriceColumn(),
            $this->createStockQuantityColumn(),
            $this->createIsActiveColumn(),
            $this->createIsFeaturedColumn(),
            $this->createCreatedAtColumn(),
        ];
    }

    /**
     * Создать все фильтры таблицы
     */
    public function createTableFilters(): array
    {
        return [
            $this->createActiveFilter(),
            $this->createFeaturedFilter(),
            $this->createLowStockFilter(),
            $this->createOutOfStockFilter(),
        ];
    }

    /**
     * Создать действия заголовка таблицы
     */
    public function createHeaderActions(): array
    {
        return [
            Tables\Actions\CreateAction::make()
                ->label('Добавить товар'),
        ];
    }

    /**
     * Создать действия строк таблицы
     */
    public function createRowActions(): array
    {
        return [
            Tables\Actions\EditAction::make()
                ->label('Редактировать'),
            Tables\Actions\DeleteAction::make()
                ->label('Удалить'),
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
     * Создать колонку изображений
     */
    private function createImageColumn(): Tables\Columns\ImageColumn
    {
        return Tables\Columns\ImageColumn::make('images')
            ->label('Фото')
            ->circular()
            ->stacked()
            ->limit(3);
    }

    /**
     * Создать колонку названия
     */
    private function createNameColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('name')
            ->label('Название')
            ->searchable()
            ->sortable()
            ->weight('bold')
            ->description(fn ($record) => $record->sku ? "Артикул: {$record->sku}" : null);
    }

    /**
     * Создать колонку цены
     */
    private function createPriceColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('price')
            ->label('Цена')
            ->money('RUB')
            ->sortable();
    }

    /**
     * Создать колонку остатка
     */
    private function createStockQuantityColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('stock_quantity')
            ->label('Остаток')
            ->alignCenter()
            ->badge()
            ->color(fn (int $state): string => match (true) {
                $state === 0 => 'danger',
                $state <= 5 => 'warning',
                default => 'success',
            });
    }

    /**
     * Создать колонку активности
     */
    private function createIsActiveColumn(): Tables\Columns\IconColumn
    {
        return Tables\Columns\IconColumn::make('is_active')
            ->label('Активен')
            ->boolean();
    }

    /**
     * Создать колонку рекомендуемого товара
     */
    private function createIsFeaturedColumn(): Tables\Columns\IconColumn
    {
        return Tables\Columns\IconColumn::make('is_featured')
            ->label('Рекомендуемый')
            ->boolean()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * Создать колонку даты создания
     */
    private function createCreatedAtColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('created_at')
            ->label('Создан')
            ->dateTime('d.m.Y')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * Создать фильтр по активности
     */
    private function createActiveFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('is_active')
            ->label('Статус')
            ->boolean()
            ->trueLabel('Только активные')
            ->falseLabel('Только неактивные')
            ->native(false);
    }

    /**
     * Создать фильтр по рекомендуемым товарам
     */
    private function createFeaturedFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('is_featured')
            ->label('Рекомендуемые')
            ->boolean()
            ->trueLabel('Только рекомендуемые')
            ->falseLabel('Только обычные')
            ->native(false);
    }

    /**
     * Создать фильтр товаров с низким остатком
     */
    private function createLowStockFilter(): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make('low_stock')
            ->label('Заканчивается')
            ->query(fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 5));
    }

    /**
     * Создать фильтр товаров без остатка
     */
    private function createOutOfStockFilter(): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make('out_of_stock')
            ->label('Нет в наличии')
            ->query(fn (Builder $query): Builder => $query->where('stock_quantity', 0));
    }
}
