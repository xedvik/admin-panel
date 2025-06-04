<?php

namespace App\Services\Tables;

use App\Models\Category;
use Filament\Tables;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Repositories\CategoryRepositoryInterface;

class CategoryTableElementsFactory
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Создать все колонки таблицы категорий
     */
    public function createTableColumns(): array
    {
        return [
            $this->createImageColumn(),
            $this->createNameColumn(),
            $this->createParentColumn(),
            $this->createProductsCountColumn(),
            $this->createSortOrderColumn(),
            $this->createActiveColumn(),
            $this->createCreatedAtColumn(),
        ];
    }

    /**
     * Создать колонку изображения
     */
    public function createImageColumn(): Tables\Columns\ImageColumn
    {
        return Tables\Columns\ImageColumn::make('image')
            ->label('Изображение')
            ->circular()
            ->size(40);
    }

    /**
     * Создать колонку названия
     */
    public function createNameColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('name')
            ->label('Название')
            ->searchable()
            ->sortable()
            ->description(fn (Category $record): string => $record->slug);
    }

    /**
     * Создать колонку родительской категории
     */
    public function createParentColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('parent.name')
            ->label('Родительская')
            ->sortable()
            ->badge()
            ->color('gray');
    }

    /**
     * Создать колонку количества товаров
     */
    public function createProductsCountColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('products_count')
            ->label('Товаров')
            ->counts('products')
            ->badge()
            ->color('success');
    }

    /**
     * Создать колонку порядка сортировки
     */
    public function createSortOrderColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('sort_order')
            ->label('Порядок')
            ->sortable()
            ->alignCenter();
    }

    /**
     * Создать все фильтры таблицы
     */
    public function createTableFilters(): array
    {
        return [
            $this->createActiveFilter(),
            $this->createParentFilter(),
            $this->createRootCategoriesFilter(),
            $this->createWithProductsFilter(),
        ];
    }

    /**
     * Создать все действия строк таблицы
     */
    public function createRowActions(): array
    {
        return [
            ...$this->createStandardActions(),
            $this->createViewProductsAction(),
        ];
    }

    /**
     * Создать действие просмотра товаров категории
     */
    public function createViewProductsAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('viewProducts')
            ->label('Товары категории')
            ->icon('heroicon-o-cube')
            ->color('info')
            ->url(function (Category $record): string {
                return app(\App\Filament\Resources\ProductResource::class)::getUrl('index', [
                    'tableFilters' => ['category_id' => ['value' => $record->id]]
                ]);
            });
    }

    /**
     * Создать фильтр активности категорий
     */
    public function createActiveFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('is_active')
            ->label('Активные')
            ->boolean()
            ->trueLabel('Только активные')
            ->falseLabel('Только неактивные')
            ->query(function (Builder $query, array $data): Builder {
                if ($data['value'] === true) {
                    $activeIds = $this->categoryRepository->getActive()->pluck('id');
                    return $query->whereIn('id', $activeIds);
                } elseif ($data['value'] === false) {
                    $activeIds = $this->categoryRepository->getActive()->pluck('id');
                    return $query->whereNotIn('id', $activeIds);
                }
                return $query;
            })
            ->native(false);
    }

    /**
     * Создать стандартные действия для категорий
     */
    public function createStandardActions(): array
    {
        return [
            Tables\Actions\ViewAction::make()
                ->label('Просмотр'),

            Tables\Actions\EditAction::make()
                ->label('Редактировать'),
        ];
    }

    /**
     * Создать массовые действия активации/деактивации категорий
     */
    public function createBulkToggleActions(): array
    {
        return [
            Tables\Actions\BulkAction::make('activate')
                ->label('Активировать категории')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(function ($records): void {
                    $count = 0;

                    foreach ($records as $record) {
                        $this->categoryRepository->update($record->id, ['is_active' => true]);
                        $count++;
                    }

                    Notification::make()
                        ->title("Активировано категорий: {$count}")
                        ->success()
                        ->send();
                }),

            Tables\Actions\BulkAction::make('deactivate')
                ->label('Деактивировать категории')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function ($records): void {
                    $count = 0;

                    foreach ($records as $record) {
                        $this->categoryRepository->update($record->id, ['is_active' => false]);
                        $count++;
                    }

                    Notification::make()
                        ->title("Деактивировано категорий: {$count}")
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * Создать стандартные массовые действия для категорий
     */
    public function createStandardBulkActions(): Tables\Actions\BulkActionGroup
    {
        return Tables\Actions\BulkActionGroup::make([
            Tables\Actions\DeleteBulkAction::make()
                ->label('Удалить'),

            ...$this->createBulkToggleActions(),
        ]);
    }

    /**
     * Создать колонку активности категории
     */
    public function createActiveColumn(): Tables\Columns\IconColumn
    {
        return Tables\Columns\IconColumn::make('is_active')
            ->label('Активен')
            ->boolean()
            ->trueIcon('heroicon-o-check-badge')
            ->falseIcon('heroicon-o-x-mark');
    }

    /**
     * Создать колонку даты создания
     */
    public function createCreatedAtColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('created_at')
            ->label('Создан')
            ->dateTime('d.m.Y H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * Создать фильтр корневых категорий
     */
    public function createRootCategoriesFilter(): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make('root_categories')
            ->label('Корневые категории')
            ->query(function (Builder $query): Builder {
                $rootIds = $this->categoryRepository->getRoot()->pluck('id');
                return $query->whereIn('id', $rootIds);
            });
    }

    /**
     * Создать фильтр категорий с товарами
     */
    public function createWithProductsFilter(): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make('with_products')
            ->label('С товарами')
            ->query(function (Builder $query): Builder {
                $categoriesWithProducts = $this->categoryRepository->getWithProductsCount()->filter(function($category) {
                    return $category->products_count > 0;
                });
                $categoryIds = $categoriesWithProducts->pluck('id');
                return $query->whereIn('id', $categoryIds);
            });
    }

    /**
     * Создать фильтр по родительской категории
     */
    public function createParentFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('parent_id')
            ->label('Родительская категория')
            ->relationship('parent', 'name')
            ->searchable()
            ->preload();
    }
}
