<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Contracts\Repositories\ProductRepositoryInterface;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?string $heading = 'Товары с низким остатком';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $productRepository = app(ProductRepositoryInterface::class);

        return $table
            ->query(
                $productRepository->getQuery()
                    ->with(['category'])
                    ->where('is_active', true)
                    ->where('stock_quantity', '<=', 5)
                    ->where('stock_quantity', '>', 0)
                    ->orderBy('stock_quantity', 'asc')
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Фото')
                    ->getStateUsing(function (?Product $record) {
                        if (!$record) return null;
                        $productRepository = app(ProductRepositoryInterface::class);
                        return $productRepository->getMainImage($record);
                    })
                    ->size(40)
                    ->circular(),

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

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Остаток')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= 2 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Просмотр')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.edit', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('restock')
                    ->label('Пополнить')
                    ->icon('heroicon-m-plus')
                    ->color('success')
                    ->action(function (Product $record, array $data): void {
                        $productRepository = app(ProductRepositoryInterface::class);
                        $productRepository->incrementStock($record->id, $data['quantity'] ?? 10);
                    })
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество для пополнения')
                            ->numeric()
                            ->default(10)
                            ->required()
                            ->minValue(1),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Пополнить товар')
                    ->modalDescription('Укажите количество для пополнения остатка')
                    ->modalSubmitActionLabel('Пополнить'),
            ])
            ->emptyStateHeading('Отличные остатки!')
            ->emptyStateDescription('Все товары имеют достаточное количество на складе.')
            ->emptyStateIcon('heroicon-o-check-badge')
            ->striped();
    }
}
