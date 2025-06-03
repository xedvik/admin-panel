<?php

namespace App\Repositories;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function getByPaymentStatus(string $paymentStatus): Collection
    {
        return $this->model->where('payment_status', $paymentStatus)->get();
    }

    public function getPending(): Collection
    {
        return $this->model->where('status', 'pending')->get();
    }

    public function getProcessing(): Collection
    {
        return $this->model->where('status', 'processing')->get();
    }

    public function getShipped(): Collection
    {
        return $this->model->where('status', 'shipped')->get();
    }

    public function getDelivered(): Collection
    {
        return $this->model->where('status', 'delivered')->get();
    }

    public function getCancelled(): Collection
    {
        return $this->model->where('status', 'cancelled')->get();
    }

    public function getPaid(): Collection
    {
        return $this->model->where('payment_status', 'paid')->get();
    }

    public function searchByOrderNumber(string $search): Collection
    {
        return $this->model->where('order_number', 'like', "%{$search}%")->get();
    }

    public function getClientOrders(int $clientId): Collection
    {
        return $this->model->where('client_id', $clientId)
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    public function getClientOrdersPaginated(int $clientId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('client_id', $clientId)
                          ->orderBy('created_at', 'desc')
                          ->paginate($perPage);
    }

    public function canBeCancelled(int $orderId): bool
    {
        $order = $this->find($orderId);
        return $order && in_array($order->status, ['pending', 'processing']);
    }

    public function cancelOrder(int $orderId): bool
    {
        if (!$this->canBeCancelled($orderId)) {
            return false;
        }

        $order = $this->findOrFail($orderId);
        $order->update(['status' => 'cancelled']);

        return true;
    }

    public function markAsShipped(int $orderId): Order
    {
        $order = $this->findOrFail($orderId);
        $order->update([
            'status' => 'shipped',
            'shipped_at' => now()
        ]);

        return $order->fresh();
    }

    public function markAsDelivered(int $orderId): Order
    {
        $order = $this->findOrFail($orderId);
        $order->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);

        return $order->fresh();
    }

    public function getTotalAmount(): int
    {
        return $this->model->sum('total_amount');
    }

    public function getTotalAmountByStatus(string $status): int
    {
        return $this->model->where('status', $status)->sum('total_amount');
    }

    /**
     * Получить заказы с связанными данными
     */
    // public function getWithRelations(): Collection
    // {
    //     return $this->model->with(['client', 'orderItems.product'])->get();
    // }

    /**
     * Получить заказы за период
     */
    public function getOrdersByDateRange(Carbon|\DateTime $startDate, Carbon|\DateTime $endDate): Collection
    {
        // Если это один день, ищем за весь день
        if ($startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
            return $this->model->whereDate('created_at', $startDate->format('Y-m-d'))->get();
        }

        return $this->model->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    /**
     * Получить статистику заказов
     */
    // public function getOrderStats(): array
    // {
    //     return [
    //         'total' => $this->count(),
    //         'pending' => $this->countWhere(['status' => 'pending']),
    //         'processing' => $this->countWhere(['status' => 'processing']),
    //         'shipped' => $this->countWhere(['status' => 'shipped']),
    //         'delivered' => $this->countWhere(['status' => 'delivered']),
    //         'cancelled' => $this->countWhere(['status' => 'cancelled']),
    //         'total_amount' => $this->getTotalAmount(),
    //     ];
    // }

    /**
     * Получить query builder для заказов
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->query();
    }

    /**
     * Получить статус заказа в читаемом виде
     */
    public function getOrderStatusLabel(int $orderId): string
    {
        $order = $this->findOrFail($orderId);

        return match ($order->status) {
            'pending' => 'В ожидании',
            'processing' => 'В обработке',
            'shipped' => 'Отправлен',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменен',
            default => $order->status,
        };
    }

    /**
     * Получить статус оплаты в читаемом виде
     */
    public function getOrderPaymentStatusLabel(int $orderId): string
    {
        $order = $this->findOrFail($orderId);

        return match ($order->payment_status) {
            'pending' => 'Ожидает оплаты',
            'paid' => 'Оплачен',
            'failed' => 'Ошибка оплаты',
            'refunded' => 'Возврат',
            default => $order->payment_status,
        };
    }

    /**
     * Получить цвет для статуса заказа
     */
    public function getOrderStatusColor(int $orderId): string
    {
        $order = $this->findOrFail($orderId);

        return match ($order->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Получить количество товаров в заказе
     */
    public function getOrderTotalItems(int $orderId): int
    {
        $order = $this->findOrFail($orderId);
        return $order->orderItems()->sum('quantity');
    }

    /**
     * Получить имя клиента заказа
     */
    public function getOrderClientName(int $orderId): string
    {
        $order = $this->findOrFail($orderId);
        return $order->client->first_name . ' ' . $order->client->last_name;
    }
}
