<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => (float) $this->price,
            'total' => (float) $this->getTotal(),
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'image' => $this->product->primaryImage?->image_path,
                'stock_quantity' => $this->product->stock_quantity,
                'stock_status' => $this->product->stock_status,
            ],
            'variant' => $this->when($this->variant, function () {
                return [
                    'id' => $this->variant->id,
                    'sku' => $this->variant->sku,
                    'variant_name' => $this->variant->getVariantName(),
                    'stock_quantity' => $this->variant->stock_quantity,
                ];
            }),
        ];
    }
}
