<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'effective_price' => (float) $this->getEffectivePrice(),
            'cost_price' => $this->when($request->user()?->isVendor() || $request->user()?->isAdmin(), (float) $this->cost_price),
            'is_on_sale' => $this->isOnSale(),
            'stock_quantity' => $this->stock_quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'is_low_stock' => $this->isLowStock(),
            'stock_status' => $this->stock_status,
            'weight' => $this->weight ? (float) $this->weight : null,
            'dimensions' => $this->dimensions,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'rating' => (float) $this->rating,
            'total_reviews' => $this->total_reviews,
            'total_sales' => $this->total_sales,
            'views_count' => $this->views_count,
            'meta_data' => $this->meta_data,
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),

            // Relationships
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_path' => $image->image_path,
                        'thumbnail_path' => $image->thumbnail_path,
                        'is_primary' => $image->is_primary,
                        'order' => $image->order,
                    ];
                });
            }),

            'variants' => $this->whenLoaded('variants', function () {
                return $this->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => (float) $variant->price,
                        'sale_price' => $variant->sale_price ? (float) $variant->sale_price : null,
                        'stock_quantity' => $variant->stock_quantity,
                        'attribute_values' => $variant->attribute_values,
                        'variant_name' => $variant->getVariantName(),
                        'is_active' => $variant->is_active,
                    ];
                });
            }),

            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),

            'vendor' => $this->when($this->relationLoaded('vendor'), function () {
                return [
                    'id' => $this->vendor->id,
                    'shop_name' => $this->vendor->shop_name,
                    'slug' => $this->vendor->slug,
                    'description' => $this->vendor->description,
                    'logo' => $this->vendor->logo,
                    'rating' => (float) $this->vendor->rating,
                    'total_reviews' => $this->vendor->total_reviews,
                ];
            }),

            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
