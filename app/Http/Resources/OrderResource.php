<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,

            // Статусы
            'status' => $this->status,
            'status_label' => $this->status_label,
            'payment_status' => $this->payment_status,
            'payment_status_label' => $this->payment_status_label,
            'payment_method' => $this->payment_method,

            // Суммы
            'subtotal' => (int) $this->subtotal,
            'tax_amount' => (int) $this->tax_amount,
            'shipping_amount' => (int) $this->shipping_amount,
            'discount_amount' => (int) $this->discount_amount,
            'total_amount' => (int) $this->total_amount,
            'currency' => $this->currency,

            // Форматированные суммы
            'subtotal_formatted' => '₽' . number_format($this->subtotal, 0, ',', ' '),
            'total_amount_formatted' => '₽' . number_format($this->total_amount, 0, ',', ' '),

            // Адреса
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,

            // Клиент
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client', function () {
                return new ClientResource($this->client);
            }),

            // Позиции заказа
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'total_items' => $this->total_items,

            // Даты
            'shipped_at' => $this->shipped_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),

            // Примечания
            'notes' => $this->notes,

            // Временные метки
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
