<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'total' => (float) $this->total,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'items_count' => $this->when(isset($this->items_count), $this->items_count),
            'vendor' => $this->when($this->relationLoaded('vendor'), function () {
                return [
                    'id' => $this->vendor->id,
                    'shop_name' => $this->vendor->shop_name,
                ];
            }),
        ];
    }
}
