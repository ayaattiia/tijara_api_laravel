<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table      = 'Orders';
    protected $primaryKey = 'IdOrder';
    public    $timestamps = false;

    protected $fillable = [
        'IdUser',
        'IdDeal',
        'DateTimeCommand',
        'Active',
        'PaymentStatus',
    ];

    protected $casts = [
        'Active'          => 'integer',
        'DateTimeCommand' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING   = 1;
    const STATUS_DELIVERED = 2;
    const STATUS_CONFIRMED = 3;
    const STATUS_CANCELLED = 0;

    // Computed status label
    public function getStatusAttribute(): string
    {
        return match ((int) $this->Active) {
            self::STATUS_DELIVERED => 'delivered',
            self::STATUS_CONFIRMED => 'confirmed',
            self::STATUS_CANCELLED => 'cancelled',
            default                => 'pending',
        };
    }

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class, 'IdUser', 'IdUser');
    }


    public function detail()
    {
        return $this->hasOne(OrderDetail::class, 'IdOrder', 'IdOrder');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'IdOrder', 'IdOrder');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'IdOrder', 'IdOrder');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'IdOrder', 'IdOrder');
    }
}
