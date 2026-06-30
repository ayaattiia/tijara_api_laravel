<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $table      = 'Deliveries';
    protected $primaryKey = 'IdDelivery';
    public    $timestamps = false;

    protected $fillable = [
        'IdOrder',
        'IdTransport',
        'TrackingNumber',
        'Status',
        'AddressLine',
        'City',
        'PostalCode',
        'Phone',
        'DeliveryFee',
        'Note',
        'EstimatedAt',
        'DeliveredAt',
        'CreatedAt',
        'UpdatedAt',
    ];

    protected $casts = [
        'DeliveryFee' => 'decimal:3',
        'EstimatedAt' => 'datetime',
        'DeliveredAt' => 'datetime',
        'CreatedAt'   => 'datetime',
        'UpdatedAt'   => 'datetime',
    ];

    // Relationships

    public function order()
    {
        return $this->belongsTo(Order::class, 'IdOrder', 'IdOrder');
    }

    public function transport()
    {
        return $this->belongsTo(Transport::class, 'IdTransport', 'IdTransport');
    }
}
