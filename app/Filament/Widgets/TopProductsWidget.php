<?php

namespace App\Filament\Widgets;

use App\Contracts\Repositories\ProductRepositoryInterface;
use Filament\Widgets\ChartWidget;

class TopProductsWidget extends ChartWidget
{
    protected static ?string $heading = 'Топ 5 товаров по продажам';

    protected static ?int $sort = 6;

    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $productRepository = app(ProductRepositoryInterface::class);
        $topProducts = $productRepository->getPopularProducts(5);

        $labels = $topProducts->pluck('name')->toArray();
        $data = $topProducts->pluck('order_items_count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Продано штук',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                    'borderColor' => [
                        'rgba(59, 130, 246, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(168, 85, 247, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                    ],
                ],
                'y' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return "Продано: " + context.parsed.x + " шт."; }',
                    ],
                ],
            ],
        ];
    }
}
