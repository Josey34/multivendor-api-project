<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    // Relationships

    /**
     * Get the user that owns this cart
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all items in this cart
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // Helper Methods

    /**
     * Get the total number of items in cart
     */
    public function getTotalItems(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Get the cart subtotal (sum of all item totals)
     */
    public function getSubtotal(): float
    {
        return $this->items()->get()->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Clear all items from cart
     */
    public function clear()
    {
        $this->items()->delete();
    }
}
