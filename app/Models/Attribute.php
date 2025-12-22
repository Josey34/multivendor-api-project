<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
    ];

    // Relationships

    /**
     * Get all values for this attribute
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
