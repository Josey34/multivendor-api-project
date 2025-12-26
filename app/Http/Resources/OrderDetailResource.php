<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'shipping_cost' => (float) $this->shipping_cost,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'tracking_number' => $this->tracking_number,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'shipped_at' => $this->shipped_at?->format('Y-m-d H:i:s'),
            'delivered_at' => $this->delivered_at?->format('Y-m-d H:i:s'),
            'cancelled_at' => $this->cancelled_at?->format('Y-m-d H:i:s'),

            'items' => OrderItemResource::collection($this->whenLoaded('items')),

            'shipping_address' => new AddressResource($this->whenLoaded('shippingAddress')),
            'billing_address' => $this->when(
                $this->relationLoaded('billingAddress') && $this->billingAddress,
                new AddressResource($this->billingAddress)
            ),

            'vendor' => $this->when($this->relationLoaded('vendor'), function () {
                return [
                    'id' => $this->vendor->id,
                    'shop_name' => $this->vendor->shop_name,
                    'phone' => $this->vendor->phone,
                ];
            }),
        ];
    }
}
