<?php

namespace App\Services\Tables;

use App\Models\Product;
use App\Contracts\Repositories\ProductRepositoryInterface;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class ProductTableComponentsFactory
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Создать колонки для таблицы товаров
     */
    public function createTableColumns(): array
    {
        return [
            Tables\Columns\ImageColumn::make('main_image')
                ->label('Фото')
                ->getStateUsing(fn (?Product $record) => $record ? $this->productRepository->getMainImage($record) : null)
                ->size(50),

            Tables\Columns\TextColumn::make('name')
                ->label('Название')
                ->searchable()
                ->sortable()
                ->limit(30)
                ->description(fn (?Product $record): string => $record?->sku ?? ''),

            Tables\Columns\TextColumn::make('category.name')
                ->label('Категория')
                ->sortable()
                ->badge()
                ->color('gray'),

            Tables\Columns\TextColumn::make('price')
                ->label('Цена')
                ->money('RUB')
                ->sortable(),

            $this->createStockQuantityColumn(),
            $this->createDiscountColumn(),
            $this->createStockStatusColumn(),

            Tables\Columns\IconColumn::make('is_active')
                ->label('Активен')
                ->boolean()
                ->trueIcon('heroicon-o-check-badge')
                ->falseIcon('heroicon-o-x-mark'),

            Tables\Columns\IconColumn::make('is_featured')
                ->label('Рекомендуемый')
                ->boolean()
                ->trueIcon('heroicon-o-star')
                ->falseIcon('heroicon-o-star')
                ->trueColor('warning'),

            Tables\Columns\TextColumn::make('promotions')
                ->label('Акции')
                ->getStateUsing(function (?Product $record) {
                    if (!$record) return '';
                    return $record->promotions
                        ->where('is_active', true)
                        ->pluck('name')
                        ->implode(', ');
                })
                ->limit(40)
                ->toggleable(isToggledHiddenByDefault: false),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Создан')
                ->dateTime('d.m.Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Создать фильтры для таблицы товаров
     */
    public function createTableFilters(): array
    {
        return [
            $this->createActiveFilter(),
            $this->createFeaturedFilter(),

            Tables\Filters\SelectFilter::make('category_id')
                ->label('Категория')
                ->relationship('category', 'name')
                ->searchable()
                ->preload(),

            $this->createOutOfStockFilter(),
            $this->createLowStockFilter(),
            $this->createInStockFilter(),
        ];
    }

    /**
     * Создать действия для строк таблицы
     */
    public function createRowActions(): array
    {
        return [
            Tables\Actions\ViewAction::make()
                ->label('Просмотр'),

            Tables\Actions\EditAction::make()
                ->label('Редактировать'),

            $this->createUpdateStockAction(),
            $this->createAddStockAction(),
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
                    ->label('Удалить'),

                $this->createActivateProductsAction(),
                $this->createDeactivateProductsAction(),
                $this->createMarkAsFeaturedAction(),
            ]),
        ];
    }

    /**
     * Создать колонку количества на складе
     */
    private function createStockQuantityColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('stock_quantity')
            ->label('Количество')
            ->numeric()
            ->sortable()
            ->alignCenter()
            ->badge()
            ->color(function (?Product $record): string {
                if (!$record || !$record->track_quantity) return 'gray';
                $quantity = $record->stock_quantity ?? 0;
                if ($quantity <= 0) return 'danger';
                if ($quantity <= 5) return 'warning';
                return 'success';
            })
            ->formatStateUsing(function (?Product $record): string {
                if (!$record || !$record->track_quantity) {
                    return 'Не отслеживается';
                }
                return (string) ($record->stock_quantity ?? 0);
            });
    }

    /**
     * Создать колонку скидки
     */
    private function createDiscountColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('discount_percentage')
            ->label('Скидка')
            ->getStateUsing(fn (?Product $record) => $record ? $this->productRepository->calculateDiscountPercent($record) : 0)
            ->suffix('%')
            ->badge()
            ->color('success')
            ->visible(fn (?Product $record) => $record ? $this->productRepository->calculateDiscountPercent($record) > 0 : false);
    }

    /**
     * Создать колонку статуса остатков
     */
    private function createStockStatusColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('stock_status')
            ->label('Остатки')
            ->getStateUsing(fn (?Product $record) => $record ? $this->productRepository->getStockStatus($record) : '')
            ->badge()
            ->color(function (string $state): string {
                return match ($state) {
                    'В наличии' => 'success',
                    'Мало в наличии' => 'warning',
                    'Нет в наличии' => 'danger',
                    'Под заказ' => 'info',
                    default => 'gray',
                };
            });
    }

    /**
     * Создать фильтр активных товаров
     */
    private function createActiveFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('is_active')
            ->label('Активные')
            ->boolean()
            ->trueLabel('Только активные')
            ->falseLabel('Только неактивные')
            ->query(function (Builder $query, array $data): Builder {
                if ($data['value'] === true) {
                    $productIds = $this->productRepository->getActive()->pluck('id');
                    return $query->whereIn('id', $productIds);
                } elseif ($data['value'] === false) {
                    $activeIds = $this->productRepository->getActive()->pluck('id');
                    return $query->whereNotIn('id', $activeIds);
                }
                return $query;
            })
            ->native(false);
    }

    /**
     * Создать фильтр рекомендуемых товаров
     */
    private function createFeaturedFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('is_featured')
            ->label('Рекомендуемые')
            ->boolean()
            ->trueLabel('Только рекомендуемые')
            ->falseLabel('Только обычные')
            ->query(function (Builder $query, array $data): Builder {
                if ($data['value'] === true) {
                    $productIds = $this->productRepository->getFeatured()->pluck('id');
                    return $query->whereIn('id', $productIds);
                } elseif ($data['value'] === false) {
                    $featuredIds = $this->productRepository->getFeatured()->pluck('id');
                    return $query->whereNotIn('id', $featuredIds);
                }
                return $query;
            })
            ->native(false);
    }

    /**
     * Создать фильтр товаров не в наличии
     */
    private function createOutOfStockFilter(): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make('out_of_stock')
            ->label('Нет в наличии')
            ->query(function (Builder $query): Builder {
                $outOfStockIds = $this->productRepository->getOutOfStockProducts()->pluck('id');
                return $query->whereIn('id', $outOfStockIds);
            });
    }

    /**
     * Создать фильтр товаров с малыми остатками
     */
    private function createLowStockFilter(): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make('low_stock')
            ->label('Мало в наличии')
            ->query(function (Builder $query): Builder {
                $lowStockIds = $this->productRepository->getLowStockProducts()->pluck('id');
                return $query->whereIn('id', $lowStockIds);
            });
    }

    /**
     * Создать фильтр товаров в наличии
     */
    private function createInStockFilter(): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make('in_stock')
            ->label('В наличии')
            ->query(function (Builder $query): Builder {
                $inStockIds = $this->productRepository->getInStock()->pluck('id');
                return $query->whereIn('id', $inStockIds);
            });
    }

    /**
     * Создать действие обновления остатков
     */
    private function createUpdateStockAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('updateStock')
            ->label('Обновить остатки')
            ->icon('heroicon-o-cube')
            ->color('warning')
            ->form([
                \Filament\Forms\Components\TextInput::make('stock_quantity')
                    ->label('Новое количество')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
            ])
            ->action(function (Product $record, array $data): void {
                $this->productRepository->updateStock($record->id, $data['stock_quantity']);

                Notification::make()
                    ->title('Остатки обновлены')
                    ->success()
                    ->send();
            });
    }

    /**
     * Создать действие добавления остатков
     */
    private function createAddStockAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('addStock')
            ->label('Добавить остатки')
            ->icon('heroicon-o-plus')
            ->color('success')
            ->form([
                \Filament\Forms\Components\TextInput::make('quantity')
                    ->label('Количество для добавления')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
            ])
            ->action(function (Product $record, array $data): void {
                $this->productRepository->incrementStock($record->id, $data['quantity']);

                Notification::make()
                    ->title("Добавлено {$data['quantity']} единиц товара")
                    ->success()
                    ->send();
            });
    }

    /**
     * Создать массовое действие активации товаров
     */
    private function createActivateProductsAction(): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make('activateProducts')
            ->label('Активировать товары')
            ->icon('heroicon-o-check')
            ->color('success')
            ->requiresConfirmation()
            ->action(function ($records): void {
                $count = 0;

                foreach ($records as $record) {
                    $this->productRepository->update($record->id, ['is_active' => true]);
                    $count++;
                }

                Notification::make()
                    ->title("Активировано товаров: {$count}")
                    ->success()
                    ->send();
            });
    }

    /**
     * Создать массовое действие деактивации товаров
     */
    private function createDeactivateProductsAction(): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make('deactivateProducts')
            ->label('Деактивировать товары')
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function ($records): void {
                $count = 0;

                foreach ($records as $record) {
                    $this->productRepository->update($record->id, ['is_active' => false]);
                    $count++;
                }

                Notification::make()
                    ->title("Деактивировано товаров: {$count}")
                    ->success()
                    ->send();
            });
    }

    /**
     * Создать массовое действие отметки как рекомендуемых
     */
    private function createMarkAsFeaturedAction(): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make('markAsFeatured')
            ->label('Сделать рекомендуемыми')
            ->icon('heroicon-o-star')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function ($records): void {
                $count = 0;

                foreach ($records as $record) {
                    $this->productRepository->update($record->id, ['is_featured' => true]);
                    $count++;
                }

                Notification::make()
                    ->title("Отмечено как рекомендуемые: {$count} товаров")
                    ->success()
                    ->send();
            });
    }
}
