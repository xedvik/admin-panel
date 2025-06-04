<?php

namespace App\Services\Business;

use App\Contracts\Repositories\ClientAddressRepositoryInterface;

class ClientAddressService
{
    public function __construct(
        private ClientAddressRepositoryInterface $addressRepository
    ) {}

    /**
     * Получить адреса клиента по типу
     */
    public function getAddressesByType(int $clientId, string $type): array
    {
        return $this->addressRepository->getByClientAndType($clientId, $type)->toArray();
    }

    /**
     * Получить основной адрес по типу
     */
    public function getDefaultAddress(int $clientId, string $type): ?array
    {
        $address = $this->addressRepository->getDefaultAddress($clientId, $type);
        return $address ? $address->toArray() : null;
    }

    /**
     * Получить все адреса доставки
     */
    public function getShippingAddresses(int $clientId): array
    {
        return $this->addressRepository->getShippingAddresses($clientId)->toArray();
    }

    /**
     * Получить все адреса оплаты
     */
    public function getBillingAddresses(int $clientId): array
    {
        return $this->addressRepository->getBillingAddresses($clientId)->toArray();
    }

    /**
     * Отформатировать адрес для отображения
     */
    public function formatAddressForDisplay(int $addressId): string
    {
        return $this->addressRepository->formatAddressForDisplay($addressId);
    }

    /**
     * Получить опции адресов для селекта
     */
    public function getAddressOptionsForSelect(int $clientId, string $type): array
    {
        return $this->addressRepository->getAddressOptionsForSelect($clientId, $type);
    }

    /**
     * Получить краткое описание адреса
     */
    public function getAddressShortDescription(int $addressId): string
    {
        return $this->addressRepository->getShortDescription($addressId);
    }

    /**
     * Получить полное имя получателя
     */
    public function getAddressFullName(int $addressId): string
    {
        return $this->addressRepository->getAddressFullName($addressId);
    }

    /**
     * Получить полный адрес
     */
    public function getFullAddress(int $addressId): string
    {
        return $this->addressRepository->getFullAddress($addressId);
    }

    /**
     * Установить адрес как основной
     */
    public function setAsDefault(int $addressId): bool
    {
        return $this->addressRepository->setAsDefault($addressId);
    }
}
