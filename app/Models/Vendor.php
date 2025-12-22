<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'shop_name',
        'slug',
        'description',
        'logo',
        'banner',
        'phone',
        'address',
        'commission_rate',
        'status',
        'rating',
        'total_reviews',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    // Relationships

    /**
     * Get the user that owns this vendor
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get vendor settings
     */
    public function settings()
    {
        return $this->hasOne(VendorSetting::class);
    }

    /**
     * Get all products for this vendor
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all orders for this vendor
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scopes

    /**
     * Scope to get only approved vendors
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get only pending vendors
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Helper Methods

    /**
     * Check if vendor is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
