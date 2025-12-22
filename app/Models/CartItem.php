<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relationships

    /**
     * Get the cart that owns this item
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant (if applicable)
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // Helper Methods

    /**
     * Get the total price for this cart item (price × quantity)
     */
    public function getTotal(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Update quantity
     */
    public function updateQuantity(int $quantity)
    {
        $this->update(['quantity' => $quantity]);
    }

    /**
     * Increment quantity
     */
    public function incrementQuantity(int $amount = 1)
    {
        $this->increment('quantity', $amount);
    }

    /**
     * Decrement quantity
     */
    public function decrementQuantity(int $amount = 1)
    {
        $newQuantity = $this->quantity - $amount;

        if ($newQuantity <= 0) {
            $this->delete();
        } else {
            $this->decrement('quantity', $amount);
        }
    }
}
