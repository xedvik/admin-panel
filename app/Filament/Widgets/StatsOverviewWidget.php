<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ClientRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $orderRepository = app(OrderRepositoryInterface::class);
        $clientRepository = app(ClientRepositoryInterface::class);
        $productRepository = app(ProductRepositoryInterface::class);

        return [
            Stat::make('Всего заказов', $orderRepository->count())
                ->description('Общее количество заказов')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Общий доход', '₽' . number_format($orderRepository->getPaid()->sum('total_amount'), 0, ',', ' '))
                ->description('Сумма оплаченных заказов')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([15, 4, 10, 2, 12, 4, 18]),

            Stat::make('Клиентов', $clientRepository->count())
                ->description('Зарегистрированных клиентов')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning')
                ->chart([2, 4, 6, 8, 10, 12, 14]),

            Stat::make('Товаров', $productRepository->getActive()->count())
                ->description('Активных товаров в каталоге')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->chart([1, 3, 5, 7, 9, 11, 13]),

            Stat::make('Заказы сегодня', $orderRepository->getOrdersByDateRange(today(), today())->count())
                ->description('Новых заказов за сегодня')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Доход сегодня', '₽' . number_format(
                $orderRepository->getOrdersByDateRange(today(), today())
                    ->filter(fn($order) => $order->payment_status === 'paid')
                    ->sum('total_amount'),
                0, ',', ' '
            ))
                ->description('Оплаченные заказы за сегодня')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Нет в наличии', $productRepository->getOutOfStockProducts()->count())
                ->description('Товаров закончилось на складе')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Средний чек', '₽' . number_format(
                $orderRepository->getPaid()->avg('total_amount') ?? 0,
                0, ',', ' '
            ))
                ->description('Среднее значение заказа')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }
}
