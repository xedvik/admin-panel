<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TotalOrdersWidget extends ChartWidget
{
    protected static ?string $heading = 'Заказы за последние 7 дней';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = $this->getOrdersPerDay();

        return [
            'datasets' => [
                [
                    'label' => 'Заказы',
                    'data' => $data['orders'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getOrdersPerDay(): array
    {
        $labels = [];
        $orders = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('d.m');

            $orderCount = Order::whereDate('created_at', $date->format('Y-m-d'))->count();
            $orders[] = $orderCount;
        }

        return [
            'labels' => $labels,
            'orders' => $orders,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
