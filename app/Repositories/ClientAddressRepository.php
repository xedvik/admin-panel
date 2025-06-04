<?php

namespace App\Repositories;

use App\Contracts\Repositories\ClientAddressRepositoryInterface;
use App\Models\ClientAddress;
use Illuminate\Database\Eloquent\Collection;

class ClientAddressRepository extends BaseRepository implements ClientAddressRepositoryInterface
{
    public function __construct(ClientAddress $model)
    {
        parent::__construct($model);
    }

    public function getByClientAndType(int $clientId, string $type): Collection
    {
        return $this->model->where('client_id', $clientId)
            ->where('type', $type)
            ->get();
    }

    public function getDefaultAddress(int $clientId, string $type): ?ClientAddress
    {
        // Сначала ищем помеченный как основной
        $defaultAddress = $this->model->where('client_id', $clientId)
            ->where('type', $type)
            ->where('is_default', true)
            ->first();

        if ($defaultAddress) {
            return $defaultAddress;
        }

        // Если основного нет, возвращаем первый
        return $this->model->where('client_id', $clientId)
            ->where('type', $type)
            ->first();
    }

    public function getShippingAddresses(int $clientId): Collection
    {
        return $this->getByClientAndType($clientId, 'shipping');
    }

    public function getBillingAddresses(int $clientId): Collection
    {
        return $this->getByClientAndType($clientId, 'billing');
    }

    public function getAddressFullName(int $addressId): string
    {
        $address = $this->find($addressId);
        if (!$address) {
            return '';
        }

        return trim($address->first_name . ' ' . $address->last_name);
    }

    public function getFullAddress(int $addressId): string
    {
        $address = $this->find($addressId);
        if (!$address) {
            return '';
        }

        $parts = array_filter([
            $address->street,
            $address->city,
            $address->state,
            $address->postal_code,
            $address->country !== 'Russia' ? $address->country : null,
        ]);

        return implode(', ', $parts);
    }

    public function getShortDescription(int $addressId): string
    {
        $address = $this->find($addressId);
        if (!$address) {
            return 'Без названия';
        }

        $label = $address->label ?: '';
        $city = $address->city ?: '';

        if ($label && $city) {
            return "{$label} ({$city})";
        }

        return $label ?: $city ?: 'Без названия';
    }

    public function getTypeName(int $addressId): string
    {
        $address = $this->find($addressId);
        if (!$address) {
            return '';
        }

        return match ($address->type) {
            'shipping' => 'Доставка',
            'billing' => 'Оплата',
            default => $address->type,
        };
    }

    public function formatAddressForDisplay(int $addressId): string
    {
        $address = $this->find($addressId);
        if (!$address) {
            return '';
        }

        $parts = [];

        if (!empty($address->label)) {
            $parts[] = $address->label;
        }

        if (!empty($address->street)) {
            $parts[] = $address->street;
        }

        if (!empty($address->city)) {
            $parts[] = $address->city;
        }

        return implode(', ', $parts);
    }

    public function getAddressOptionsForSelect(int $clientId, string $type): array
    {
        $addresses = $this->getByClientAndType($clientId, $type);
        $options = [];

        foreach ($addresses as $address) {
            $label = $this->formatAddressForDisplay($address->id);

            if ($address->is_default) {
                $label .= ' (основной)';
            }

            $options[$address->id] = $label;
        }

        return $options;
    }

    public function setAsDefault(int $addressId): bool
    {
        $address = $this->find($addressId);
        if (!$address) {
            return false;
        }

        // Убираем флаг основного у других адресов того же типа
        $this->clearDefaultFlag($address->client_id, $address->type, $addressId);

        // Устанавливаем флаг основного для текущего адреса
        return $address->update(['is_default' => true]);
    }

    public function clearDefaultFlag(int $clientId, string $type, ?int $excludeAddressId = null): bool
    {
        $query = $this->model->where('client_id', $clientId)
            ->where('type', $type);

        if ($excludeAddressId) {
            $query->where('id', '!=', $excludeAddressId);
        }

        return $query->update(['is_default' => false]) !== false;
    }
}
