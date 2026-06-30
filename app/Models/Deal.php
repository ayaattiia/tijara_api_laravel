<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $table      = 'Deals';
    protected $primaryKey = 'IdDeal';
    public    $timestamps = false;

    protected $fillable = [
        'IdUser',
        'IdCategory',
        'titleDeal',
        'descriptionDeal',
        'priceDeal',
        'imageDeal',
        'EntrepriseName',
        'Stock',
        'SKU',
        'Barcode',
        'active',
        'CreatedAt',
        'UpdatedAt',
    ];

    protected $casts = [
        'priceDeal'  => 'decimal:3',
        'Stock'      => 'integer',
        'active'     => 'boolean',
        'CreatedAt'  => 'datetime',
        'UpdatedAt'  => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────

    public function vendor()
    {
        return $this->belongsTo(User::class, 'IdUser', 'IdUser');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'IdCategory', 'IdCateg');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'IdDeal', 'IdDeal');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'TargetId', 'IdDeal')
                    ->where('TargetType', 'deal');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function getPriceAttribute(): float
    {
        return (float) str_replace(',', '.', $this->priceDeal ?? 0);
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->reviews()->avg('Rating'), 1);
    }

    public function decrementStock(int $qty = 1): void
    {
        if ($this->Stock > 0) {
            $this->decrement('Stock', $qty);
        }
    }
}
