<?php

namespace App\Contracts\Repositories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

interface ClientRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить только активных клиентов
     */
    public function getActive(): Collection;

    /**
     * Получить клиентов с подтвержденным email
     */
    public function getVerified(): Collection;

    /**
     * Получить клиентов, согласившихся на маркетинг
     */
    public function getAcceptsMarketing(): Collection;

    /**
     * Найти клиента по email
     */
    // public function findByEmail(string $email): ?Client;

    /**
     * Поиск клиентов по имени или email
     */
    public function search(string $search): Collection;

    /**
     * Получить клиентов VIP статуса
     */
    public function getVipClients(): Collection;

    /**
     * Получить постоянных клиентов
     */
    public function getRegularClients(): Collection;

    /**
     * Получить новых клиентов
     */
    public function getNewClients(): Collection;

    /**
     * Получить клиентов по общей сумме покупок
     */
    public function getClientsByTotalSpent(int $minAmount): Collection;

    /**
     * Получить клиентов по количеству заказов
     */
    public function getClientsByOrderCount(int $minOrders): Collection;

    /**
     * Обновить статус email верификации
     */
    public function markEmailAsVerified(int $clientId): Client;

    /**
     * Получить статус клиента (Новый, Обычный, Постоянный, VIP)
     */
    public function getClientStatus(int $clientId): string;

    /**
     * Получить количество заказов клиента
     */
    public function getClientOrdersCount(int $clientId): int;

    /**
     * Получить общую сумму потраченную клиентом
     */
    public function getClientTotalSpent(int $clientId): int;

    /**
     * Получить полное имя клиента
     */
    public function getClientFullName(int $clientId): string;

    /**
     * Получить основной адрес доставки клиента
     */
    public function getDefaultShippingAddress(int $clientId): ?array;

    /**
     * Получить основной адрес оплаты клиента
     */
    public function getDefaultBillingAddress(int $clientId): ?array;

    /**
     * Получить все адреса доставки клиента
     */
    public function getShippingAddresses(int $clientId): array;

    /**
     * Получить все адреса оплаты клиента
     */
    public function getBillingAddresses(int $clientId): array;
}
