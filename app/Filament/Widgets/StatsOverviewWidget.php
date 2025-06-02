<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        return [
            Stat::make('Всего заказов', Order::count())
                ->description('Общее количество заказов')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Общий доход', '₽' . number_format(Order::where('payment_status', 'paid')->sum('total_amount'), 0, ',', ' '))
                ->description('Сумма оплаченных заказов')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([15, 4, 10, 2, 12, 4, 18]),

            Stat::make('Клиентов', Client::count())
                ->description('Зарегистрированных клиентов')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning')
                ->chart([2, 4, 6, 8, 10, 12, 14]),

            Stat::make('Товаров', Product::where('is_active', true)->count())
                ->description('Активных товаров в каталоге')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->chart([1, 3, 5, 7, 9, 11, 13]),

            Stat::make('Заказы сегодня', Order::whereDate('created_at', today())->count())
                ->description('Новых заказов за сегодня')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Доход сегодня', '₽' . number_format(Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total_amount'), 0, ',', ' '))
                ->description('Оплаченные заказы за сегодня')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Нет в наличии', Product::where('is_active', true)->where('stock_quantity', 0)->count())
                ->description('Товаров закончилось на складе')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Средний чек', '₽' . number_format(Order::where('payment_status', 'paid')->avg('total_amount'), 0, ',', ' '))
                ->description('Среднее значение заказа')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }
}
