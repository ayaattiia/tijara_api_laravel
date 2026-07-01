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

    // ── Status constants ──────────────────────────────────────────
    const STATUS_CANCELLED = 0;
    const STATUS_PENDING   = 1;
    const STATUS_DELIVERED = 2;
    const STATUS_CONFIRMED = 3;
    const STATUS_REJECTED  = 4;

    public function getStatusAttribute(): string
    {
        return match ((int) $this->Active) {
            self::STATUS_DELIVERED => 'delivered',
            self::STATUS_CONFIRMED => 'confirmed',
            self::STATUS_CANCELLED => 'cancelled',
            self::STATUS_REJECTED  => 'rejected',
            default                => 'pending',
        };
    }

    // ── Relationships ────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'IdUser', 'IdUser');
    }

    /**
     * AJOUTÉ — relation deal() manquante.
     * Orders référence IdDeal → Deals, mais Order.php n'avait pas cette relation.
     * Tous les eager-loads ->with('deal') dans OrdersController échouaient silencieusement.
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class, 'IdDeal', 'IdDeal');
    }

    /** Alias : product() → même relation, pour compatibilité avec les routes /products */
    public function product()
    {
        return $this->deal();
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

    public function preInvoice()
    {
        return $this->hasOne(PreInvoice::class, 'IdOrder', 'IdOrder');
    }
}
