<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'color_code',
    ];

    // Relationships

    /**
     * Get the attribute this value belongs to
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
