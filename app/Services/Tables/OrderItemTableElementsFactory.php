<?php

namespace App\Services\Tables;

use App\Contracts\Repositories\ProductRepositoryInterface;
use Filament\Tables;

class OrderItemTableElementsFactory
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Создать все колонки таблицы позиций заказа
     */
    public function createTableColumns(): array
    {
        return [
            $this->createProductImageColumn(),
            $this->createProductNameColumn(),
            $this->createQuantityColumn(),
            $this->createProductPriceColumn(),
            $this->createTotalPriceColumn(),
            $this->createProductVariantColumn(),
            $this->createNotesColumn(),
        ];
    }

    /**
     * Создать все фильтры таблицы
     */
    public function createTableFilters(): array
    {
        return [
            $this->createProductFilter(),
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
                ->label('Изменить'),
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
     * Создать колонку изображения товара
     */
    private function createProductImageColumn(): Tables\Columns\ImageColumn
    {
        return Tables\Columns\ImageColumn::make('product_image')
            ->label('Фото')
            ->getStateUsing(function ($record) {
                $product = $this->productRepository->find($record->product_id);
                return $product ? $this->productRepository->getMainImage($product) : null;
            })
            ->circular()
            ->size(50);
    }

    /**
     * Создать колонку названия товара
     */
    private function createProductNameColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('product_name')
            ->label('Товар')
            ->searchable()
            ->weight('bold')
            ->description(fn ($record) => $record->product_sku ? "Артикул: {$record->product_sku}" : null);
    }

    /**
     * Создать колонку количества
     */
    private function createQuantityColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('quantity')
            ->label('Кол-во')
            ->alignCenter()
            ->badge()
            ->color('info');
    }

    /**
     * Создать колонку цены товара
     */
    private function createProductPriceColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('product_price')
            ->label('Цена')
            ->money('RUB')
            ->sortable();
    }

    /**
     * Создать колонку общей стоимости
     */
    private function createTotalPriceColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('total_price')
            ->label('Сумма')
            ->money('RUB')
            ->sortable()
            ->weight('bold')
            ->color('success');
    }

    /**
     * Создать колонку варианта товара
     */
    private function createProductVariantColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('product_variant')
            ->label('Вариант')
            ->limit(50)
            ->placeholder('Без вариантов')
            ->badge()
            ->color('gray');
    }

    /**
     * Создать колонку примечаний
     */
    private function createNotesColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('notes')
            ->label('Примечания')
            ->limit(50)
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * Создать фильтр по товару
     */
    private function createProductFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('product_id')
            ->label('Товар')
            ->options(function () {
                return $this->productRepository->getActive()->pluck('name', 'id');
            })
            ->searchable()
            ->preload();
    }
}
