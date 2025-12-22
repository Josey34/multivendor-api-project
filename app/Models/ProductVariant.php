<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
        'stock_quantity',
        'image',
        'attribute_values',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'attribute_values' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships

    /**
     * Get the product that owns this variant
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Helper Methods

    /**
     * Get the effective price (sale price if available, otherwise regular price)
     */
    public function getEffectivePrice()
    {
        return $this->sale_price ?? $this->price;
    }

    /**
     * Check if variant is on sale
     */
    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Get human-readable variant name
     * Example: "Red / Large"
     */
    public function getVariantName(): string
    {
        $names = [];
        foreach ($this->attribute_values as $attributeId => $valueId) {
            $value = AttributeValue::find($valueId);
            if ($value) {
                $names[] = $value->value;
            }
        }
        return implode(' / ', $names);
    }
}
