<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships

    /**
     * Get all products for this brand
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scopes

    /**
     * Scope to get only active brands
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
