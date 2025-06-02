<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TotalRevenueWidget extends ChartWidget
{
    protected static ?string $heading = 'Доходы за последние 7 дней';

    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = $this->getRevenuePerDay();

        return [
            'datasets' => [
                [
                    'label' => 'Доходы (₽)',
                    'data' => $data['revenue'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'pointBackgroundColor' => 'rgb(34, 197, 94)',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgb(34, 197, 94)',
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

    private function getRevenuePerDay(): array
    {
        $labels = [];
        $revenue = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('d.m');

            $dailyRevenue = Order::whereDate('created_at', $date->format('Y-m-d'))
                ->where('payment_status', 'paid')
                ->sum('total_amount');

            $revenue[] = (int) $dailyRevenue;
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
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
                    'ticks' => [
                        'callback' => 'function(value) { return "₽" + value.toLocaleString(); }',
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
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return "₽" + context.parsed.y.toLocaleString(); }',
                    ],
                ],
            ],
        ];
    }
}
