<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'effective_price' => (float) $this->getEffectivePrice(),
            'is_on_sale' => $this->isOnSale(),
            'stock_quantity' => $this->stock_quantity,
            'stock_status' => $this->stock_status,
            'is_featured' => $this->is_featured,
            'rating' => (float) $this->rating,
            'total_reviews' => $this->total_reviews,
            'total_sales' => $this->total_sales,
            'primary_image' => $this->whenLoaded('primaryImage', function () {
                return $this->primaryImage?->image_path;
            }),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'vendor' => $this->when($this->relationLoaded('vendor'), function () {
                return [
                    'id' => $this->vendor->id,
                    'shop_name' => $this->vendor->shop_name,
                    'slug' => $this->vendor->slug,
                    'rating' => (float) $this->vendor->rating,
                ];
            }),
        ];
    }
}
