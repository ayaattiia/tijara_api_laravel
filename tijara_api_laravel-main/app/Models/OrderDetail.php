<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table      = 'OrderDetails';
    protected $primaryKey = 'IdOrderDetail';
    public    $timestamps = false;

    protected $fillable = [
        'IdUser',
        'IdOrder',
        'Address',
        'Email',
        'Telephone',
        'FirstName',
        'LastName',
        'Quantity',
        'TotalAmount',
        'DateTimeCommand',
        'Active',
    ];

    protected $casts = [
        'Quantity'        => 'integer',
        'TotalAmount'     => 'decimal:3',
        'Active'          => 'boolean',
        'DateTimeCommand' => 'datetime',
    ];

    // Relationships

    public function order()
    {
        return $this->belongsTo(Order::class, 'IdOrder', 'IdOrder');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'IdUser', 'IdUser');
    }
}
