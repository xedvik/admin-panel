<?php

namespace App\Contracts\Repositories;

use App\Models\ClientAddress;
use Illuminate\Database\Eloquent\Collection;

interface ClientAddressRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Получить адреса клиента по типу
     */
    public function getByClientAndType(int $clientId, string $type): Collection;

    /**
     * Получить основной адрес клиента по типу
     */
    public function getDefaultAddress(int $clientId, string $type): ?ClientAddress;

    /**
     * Получить все адреса доставки клиента
     */
    public function getShippingAddresses(int $clientId): Collection;

    /**
     * Получить все адреса оплаты клиента
     */
    public function getBillingAddresses(int $clientId): Collection;

    /**
     * Получить полное имя получателя
     */
    public function getAddressFullName(int $addressId): string;

    /**
     * Получить полный адрес в одну строку
     */
    public function getFullAddress(int $addressId): string;

    /**
     * Получить краткое описание адреса
     */
    public function getShortDescription(int $addressId): string;

    /**
     * Получить название типа адреса
     */
    public function getTypeName(int $addressId): string;

    /**
     * Отформатировать адрес для отображения
     */
    public function formatAddressForDisplay(int $addressId): string;

    /**
     * Получить опции адресов для селекта
     */
    public function getAddressOptionsForSelect(int $clientId, string $type): array;

    /**
     * Установить адрес как основной
     */
    public function setAsDefault(int $addressId): bool;

    /**
     * Убрать флаг основного адреса у других адресов того же типа
     */
    public function clearDefaultFlag(int $clientId, string $type, ?int $excludeAddressId = null): bool;
}
