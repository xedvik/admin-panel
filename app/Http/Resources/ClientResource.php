<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'initials' => $this->initials,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender,

            // Адреса (только для аутентифицированного клиента)
            'addresses' => $this->when($this->shouldShowAddresses($request), $this->addresses),
            'default_address' => $this->when($this->shouldShowAddresses($request), $this->default_address),

            // Статус
            'customer_status' => $this->customer_status,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toISOString(),

            // Статистика заказов (только для самого клиента)
            'total_orders' => $this->when($this->shouldShowOrderStats($request), $this->total_orders),
            'total_spent' => $this->when($this->shouldShowOrderStats($request), $this->total_spent),

            // Временные метки
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Определяет, следует ли показывать адреса клиента
     */
    private function shouldShowAddresses(Request $request): bool
    {
        // Показывать адреса только самому клиенту или админу
        return $request->user()?->id === $this->id || $request->user()?->role === 'admin' ?? false;
    }

    /**
     * Определяет, следует ли показывать статистику заказов
     */
    private function shouldShowOrderStats(Request $request): bool
    {
        // Показывать статистику только самому клиенту или админу
        return $request->user()?->id === $this->id || $request->user()?->role === 'admin' ?? false;
    }
}
