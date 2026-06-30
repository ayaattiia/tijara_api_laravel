<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table      = 'Payments';
    protected $primaryKey = 'IdPayment';
    public    $timestamps = false;

    protected $fillable = [
        'IdUser',
        'IdOrder',
        'Amount',
        'Method',
        'Status',
        'Reference',
        'TransactionId',
        'PaidAt',
    ];

    protected $casts = [
        'Amount' => 'decimal:3',
        'PaidAt' => 'datetime',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class, 'IdUser', 'IdUser');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'IdOrder', 'IdOrder');
    }
}
