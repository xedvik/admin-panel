<?php

namespace App\Repositories;

use App\Contracts\Repositories\ClientRepositoryInterface;
use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ClientRepository extends BaseRepository implements ClientRepositoryInterface
{
    public function __construct(Client $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function getVerified(): Collection
    {
        return $this->model->whereNotNull('email_verified_at')->get();
    }

    public function getAcceptsMarketing(): Collection
    {
        return $this->model->where('accepts_marketing', true)->get();
    }

    // public function findByEmail(string $email): ?Client
    // {
    //     return $this->model->where('email', $email)->first();
    // }

    public function search(string $search): Collection
    {
        return $this->model->where(function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        })->get();
    }

    public function getVipClients(): Collection
    {
        return $this->model->whereHas('orders', function ($query) {
            $query->where('payment_status', 'paid')
                  ->havingRaw('SUM(total_amount) >= ?', [100000]);
        })
        ->orWhereHas('orders', function ($query) {
            $query->havingRaw('COUNT(*) >= ?', [10]);
        })
        ->get();
    }

    public function getRegularClients(): Collection
    {
        return $this->model->whereHas('orders', function ($query) {
            $query->havingRaw('COUNT(*) >= ? AND COUNT(*) < ?', [3, 10]);
        })
        ->whereDoesntHave('orders', function ($query) {
            $query->where('payment_status', 'paid')
                  ->havingRaw('SUM(total_amount) >= ?', [100000]);
        })
        ->get();
    }

    public function getNewClients(): Collection
    {
        return $this->model->whereDoesntHave('orders')->get();
    }

    public function getClientsByTotalSpent(int $minAmount): Collection
    {
        $minAmountInCents = $minAmount;

        return $this->model->whereHas('orders', function ($query) use ($minAmountInCents) {
            $query->where('payment_status', 'paid')
                  ->havingRaw('SUM(total_amount) >= ?', [$minAmountInCents]);
        })->get();
    }

    public function getClientsByOrderCount(int $minOrders): Collection
    {
        return $this->model->whereHas('orders', function ($query) use ($minOrders) {
            $query->havingRaw('COUNT(*) >= ?', [$minOrders]);
        })->get();
    }

    public function markEmailAsVerified(int $clientId): Client
    {
        $client = $this->findOrFail($clientId);
        $client->update(['email_verified_at' => now()]);

        return $client->fresh();
    }

    /**
     * Получить клиентов с их заказами
     */
    // public function getWithOrders(): Collection
    // {
    //     return $this->model->with('orders')->get();
    // }

    /**
     * Получить топ клиентов по сумме покупок
     */
    // public function getTopClientsBySpent(int $limit = 10): Collection
    // {
    //     return $this->model->withSum(['orders' => function ($query) {
    //         $query->where('payment_status', 'paid');
    //     }], 'total_amount')
    //     ->orderBy('orders_sum_total_amount', 'desc')
    //     ->limit($limit)
    //     ->get();
    // }

    /**
     * Получить статистику клиентов
     */
    // public function getClientStats(): array
    // {
    //     return [
    //         'total' => $this->count(),
    //         'active' => $this->countWhere(['is_active' => true]),
    //         'verified' => $this->model->whereNotNull('email_verified_at')->count(),
    //         'accepts_marketing' => $this->countWhere(['accepts_marketing' => true]),
    //         'new' => $this->model->whereDoesntHave('orders')->count(),
    //         'regular' => $this->getRegularClients()->count(),
    //         'vip' => $this->getVipClients()->count(),
    //     ];
    // }

    /**
     * Получить клиентов зарегистрированных за период
     */
    // public function getClientsByDateRange(\DateTime $startDate, \DateTime $endDate): Collection
    // {
    //     return $this->model->whereBetween('created_at', [$startDate, $endDate])->get();
    // }

    /**
     * Получить статус клиента (Новый, Обычный, Постоянный, VIP)
     */
    public function getClientStatus(int $clientId): string
    {
        $client = $this->findOrFail($clientId);

        $totalSpent = $client->orders()
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $ordersCount = $client->orders()->count();

        if ($totalSpent >= 100000 || $ordersCount >= 10) {
            return 'VIP';
        } elseif ($ordersCount >= 3) {
            return 'Постоянный';
        } elseif ($ordersCount > 0) {
            return 'Обычный';
        }

        return 'Новый';
    }

    /**
     * Получить количество заказов клиента
     */
    public function getClientOrdersCount(int $clientId): int
    {
        $client = $this->findOrFail($clientId);
        return $client->orders()->count();
    }

    /**
     * Получить общую сумму потраченную клиентом
     */
    public function getClientTotalSpent(int $clientId): int
    {
        $client = $this->findOrFail($clientId);
        return $client->orders()
            ->where('payment_status', 'paid')
            ->sum('total_amount');
    }

    /**
     * Получить полное имя клиента
     */
    public function getClientFullName(int $clientId): string
    {
        $client = $this->findOrFail($clientId);
        return $client->first_name . ' ' . $client->last_name;
    }

    /**
     * Получить основной адрес доставки клиента
     */
    public function getDefaultShippingAddress(int $clientId): ?array
    {
        $client = $this->findOrFail($clientId);
        $address = $client->clientAddresses()
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->first();

        if (!$address) {
            $address = $client->clientAddresses()
                ->where('type', 'shipping')
                ->first();
        }

        return $address ? $address->toArray() : null;
    }

    /**
     * Получить основной адрес оплаты клиента
     */
    public function getDefaultBillingAddress(int $clientId): ?array
    {
        $client = $this->findOrFail($clientId);
        $address = $client->clientAddresses()
            ->where('type', 'billing')
            ->where('is_default', true)
            ->first();

        if (!$address) {
            $address = $client->clientAddresses()
                ->where('type', 'billing')
                ->first();
        }

        return $address ? $address->toArray() : null;
    }

    /**
     * Получить все адреса доставки клиента
     */
    public function getShippingAddresses(int $clientId): array
    {
        $client = $this->findOrFail($clientId);
        return $client->clientAddresses()
            ->where('type', 'shipping')
            ->get()
            ->toArray();
    }

    /**
     * Получить все адреса оплаты клиента
     */
    public function getBillingAddresses(int $clientId): array
    {
        $client = $this->findOrFail($clientId);
        return $client->clientAddresses()
            ->where('type', 'billing')
            ->get()
            ->toArray();
    }
}
