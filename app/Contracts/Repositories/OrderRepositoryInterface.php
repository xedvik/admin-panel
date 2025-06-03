<?php

namespace App\Contracts\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить заказы по статусу
     */
    public function getByStatus(string $status): Collection;

    /**
     * Получить заказы по статусу оплаты
     */
    public function getByPaymentStatus(string $paymentStatus): Collection;

    /**
     * Получить заказы в ожидании
     */
    public function getPending(): Collection;

    /**
     * Получить заказы в обработке
     */
    public function getProcessing(): Collection;

    /**
     * Получить отправленные заказы
     */
    public function getShipped(): Collection;

    /**
     * Получить доставленные заказы
     */
    public function getDelivered(): Collection;

    /**
     * Получить отмененные заказы
     */
    public function getCancelled(): Collection;

    /**
     * Получить оплаченные заказы
     */
    public function getPaid(): Collection;

    /**
     * Поиск заказов по номеру
     */
    public function searchByOrderNumber(string $search): Collection;

    /**
     * Получить заказы клиента
     */
    public function getClientOrders(int $clientId): Collection;

    /**
     * Получить заказы клиента с пагинацией
     */
    public function getClientOrdersPaginated(int $clientId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Проверить можно ли отменить заказ
     */
    public function canBeCancelled(int $orderId): bool;

    /**
     * Отменить заказ
     */
    public function cancelOrder(int $orderId): bool;

    /**
     * Отметить заказ как отправленный
     */
    public function markAsShipped(int $orderId): Order;

    /**
     * Отметить заказ как доставленный
     */
    public function markAsDelivered(int $orderId): Order;

    /**
     * Получить общую сумму заказов
     */
    public function getTotalAmount(): int;

    /**
     * Получить общую сумму заказов по статусу
     */
    public function getTotalAmountByStatus(string $status): int;

    /**
     * Получить query builder для заказов
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder;

    /**
     * Получить статус заказа в читаемом виде
     */
    public function getOrderStatusLabel(int $orderId): string;

    /**
     * Получить статус оплаты в читаемом виде
     */
    public function getOrderPaymentStatusLabel(int $orderId): string;

    /**
     * Получить цвет для статуса заказа
     */
    public function getOrderStatusColor(int $orderId): string;

    /**
     * Получить количество товаров в заказе
     */
    public function getOrderTotalItems(int $orderId): int;

    /**
     * Получить имя клиента заказа
     */
    public function getOrderClientName(int $orderId): string;

    /**
     * Получить заказы за период
     */
    public function getOrdersByDateRange(Carbon|\DateTime $startDate, Carbon|\DateTime $endDate): Collection;
}
