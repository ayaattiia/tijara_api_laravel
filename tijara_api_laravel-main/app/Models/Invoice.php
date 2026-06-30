<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table      = 'Invoices';
    protected $primaryKey = 'IdInvoice';
    public    $timestamps = false;

    protected $fillable = [
        'Number',
        'IdOrder',
        'IdUser',
        'IdVendor',
        'Subtotal',
        'Tax',
        'DeliveryFee',
        'Total',
        'Status',
        'IssuedAt',
        'PaidAt',
    ];

    protected $casts = [
        'Subtotal'    => 'decimal:3',
        'Tax'         => 'decimal:3',
        'DeliveryFee' => 'decimal:3',
        'Total'       => 'decimal:3',
        'IssuedAt'    => 'datetime',
        'PaidAt'      => 'datetime',
    ];

    // Relationships

    public function order()
    {
        return $this->belongsTo(Order::class, 'IdOrder', 'IdOrder');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'IdUser', 'IdUser');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'IdVendor', 'IdUser');
    }
}
