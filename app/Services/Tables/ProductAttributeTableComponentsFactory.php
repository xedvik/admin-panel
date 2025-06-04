<?php

namespace App\Services\Tables;

use App\Services\Business\AttributeTypeService;
use Filament\Tables;

class ProductAttributeTableComponentsFactory
{
    public function __construct(
        private AttributeTypeService $attributeTypeService
    ) {}

    /**
     * Создать колонки для таблицы атрибутов
     */
    public function createTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Название')
                ->searchable()
                ->sortable(),

            $this->createTypeColumn(),

            Tables\Columns\TextColumn::make('values_count')
                ->label('Товаров с атрибутом')
                ->counts('values')
                ->sortable(),

            Tables\Columns\IconColumn::make('is_required')
                ->label('Обязательный')
                ->boolean(),

            Tables\Columns\IconColumn::make('is_filterable')
                ->label('В фильтрах')
                ->boolean(),

            Tables\Columns\IconColumn::make('is_active')
                ->label('Активный')
                ->boolean(),

            Tables\Columns\TextColumn::make('sort_order')
                ->label('Порядок')
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Создан')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Создать фильтры для таблицы атрибутов
     */
    public function createTableFilters(): array
    {
        return [
            $this->createTypeFilter(),
            $this->createActiveFilter(),
            $this->createRequiredFilter(),
        ];
    }

    /**
     * Создать действия для строк таблицы
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

    /**
     * Создать колонку типа атрибута
     */
    private function createTypeColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('type')
            ->label('Тип')
            ->badge()
            ->formatStateUsing(fn (string $state): string => $this->attributeTypeService->getTypeLabel($state))
            ->color(fn (string $state): string => $this->attributeTypeService->getTypeBadgeColor($state));
    }

    /**
     * Создать фильтр по типу
     */
    private function createTypeFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('type')
            ->label('Тип')
            ->options(fn () => $this->attributeTypeService->getTypeOptions());
    }

    /**
     * Создать фильтр активности
     */
    private function createActiveFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('is_active')
            ->label('Активный');
    }

    /**
     * Создать фильтр обязательности
     */
    private function createRequiredFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('is_required')
            ->label('Обязательный');
    }
}
