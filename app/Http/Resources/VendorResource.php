<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'shop_name' => $this->shop_name,
            'slug' => $this->slug,
            'description' => $this->description,
            'logo' => $this->logo,
            'banner' => $this->banner,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'rating' => (float) $this->rating,
            'total_reviews' => $this->total_reviews,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
