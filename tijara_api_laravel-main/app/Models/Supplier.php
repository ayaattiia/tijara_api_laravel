<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table      = 'Suppliers';
    protected $primaryKey = 'IdSupplier';
    public    $timestamps = false;

    protected $fillable = [
        'IdUser',
        'EntrepriseName',
        'PlatformName',
        'Email',
        'Telephone',
        'Address',
        'City',
        'Country',
        'RIB',
        'TaxNumber',
        'CommercialRegister',
        'AverageRating',
        'TotalReviews',
        'TotalProducts',
        'Active',
        'CreatedAt',
        'UpdatedAt',
    ];

    protected $casts = [
        'AverageRating' => 'decimal:2',
        'TotalReviews'  => 'integer',
        'TotalProducts' => 'integer',
        'Active'        => 'boolean',
        'CreatedAt'     => 'datetime',
        'UpdatedAt'     => 'datetime',
    ];

    // Status constants
    const STATUS_ACTIVE   = true;
    const STATUS_INACTIVE = false;

    // ── Relationships ────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'IdUser', 'IdUser');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'IdUser', 'IdUser');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'IdVendor', 'IdUser');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'IdVendor', 'IdUser');
    }

    public function preInvoices()
    {
        return $this->hasMany(PreInvoice::class, 'IdVendor', 'IdUser');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'TargetId', 'IdUser')
            ->where('TargetType', 'vendor');
    }

    // ── Helpers ────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->Active === self::STATUS_ACTIVE;
    }

    public function averageRating(): float
    {
        return round((float) $this->reviews()->avg('Rating'), 2);
    }

    public function totalReviews(): int
    {
        return $this->reviews()->count();
    }

    public function totalProducts(): int
    {
        return $this->products()->count();
    }
}
