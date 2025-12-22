<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'type',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Relationships

    /**
     * Get the user that owns this address
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper Methods

    /**
     * Get full formatted address
     */
    public function getFullAddress(): string
    {
        $parts = [
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state . ' ' . $this->postal_code,
            $this->country,
        ];

        return implode(', ', array_filter($parts));
    }

    /**
     * Set this address as default and unset others
     */
    public function setAsDefault()
    {
        // Unset all other default addresses for this user
        $this->user->addresses()->update(['is_default' => false]);

        // Set this one as default
        $this->update(['is_default' => true]);
    }
}
