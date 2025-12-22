<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'allow_cod',
        'allow_returns',
        'return_days',
        'min_order_amount',
        'business_hours',
        'shipping_methods',
    ];

    protected $casts = [
        'allow_cod' => 'boolean',
        'allow_returns' => 'boolean',
        'min_order_amount' => 'decimal:2',
        'business_hours' => 'array',
        'shipping_methods' => 'array',
    ];

    // Relationships

    /**
     * Get the vendor that owns these settings
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
