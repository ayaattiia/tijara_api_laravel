<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreInvoice extends Model
{
    protected $table      = 'PreInvoices';
    protected $primaryKey = 'IdPreInvoice';
    public    $timestamps = false;

    protected $fillable = [
        'Number',
        'IdOrder',
        'IdUser',
        'IdVendor',
        'EntrepriseName',
        'PlatformName',
        'ClientName',
        'ClientEmail',
        'ClientPhone',
        'ClientAddress',
        'Subtotal',
        'Tax',
        'DeliveryFee',
        'Discount',
        'Total',
        'Status',
        'Notes',
        'RejectionReason',
        'ConvertedToInvoice',
        'IssuedAt',
        'ApprovedAt',
        'RejectedAt',
        'ConvertedAt',
        'UpdatedAt',
    ];

    protected $casts = [
        'Subtotal'    => 'decimal:3',
        'Tax'         => 'decimal:3',
        'DeliveryFee' => 'decimal:3',
        'Discount'    => 'decimal:3',
        'Total'       => 'decimal:3',
        'IssuedAt'    => 'datetime',
        'ApprovedAt'  => 'datetime',
        'RejectedAt'  => 'datetime',
        'ConvertedAt' => 'datetime',
        'UpdatedAt'   => 'datetime',
    ];

    const STATUS_DRAFT     = 'draft';
    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_CONVERTED = 'converted';

    // ── Relationships ────────────────────────────────────────────

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

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'ConvertedToInvoice', 'IdInvoice');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function canBeApproved(): bool
    {
        return $this->Status === self::STATUS_PENDING;
    }

    public function canBeConverted(): bool
    {
        return $this->Status === self::STATUS_APPROVED;
    }
}
