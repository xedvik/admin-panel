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

    public function search(string $search): Collection
    {
        return $this->model->where(function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        })->get();
    }

    public function markEmailAsVerified(int $clientId): Client
    {
        $client = $this->findOrFail($clientId);
        $client->update(['email_verified_at' => now()]);

        return $client->fresh();
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
