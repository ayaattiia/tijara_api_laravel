<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transport extends Model
{
    protected $table      = 'Transports';
    protected $primaryKey = 'IdTransport';
    public    $timestamps = false;

    protected $fillable = [
        'Name',
        'Logo',
        'Phone',
        'Email',
        'DeliveryFee',
        'FreeFrom',
        'Zones',
        'Active',
    ];

    protected $casts = [
        'DeliveryFee' => 'decimal:3',
        'FreeFrom'    => 'decimal:3',
        'Active'      => 'boolean',
    ];

    // Relationships

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'IdTransport', 'IdTransport');
    }

    // Helper: check if free delivery applies for a given amount
    public function isFreeFor(float $amount): bool
    {
        return $this->FreeFrom > 0 && $amount >= $this->FreeFrom;
    }
}
