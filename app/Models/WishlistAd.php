<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishlistAd extends Model
{
    protected $table = 'wishlist_ads';
    protected $primaryKey = 'IdWish';
    protected $fillable = ['IdUser', 'IdAd'];
    public $timestamps = true;

    public function ad()
    {
        return $this->belongsTo(Ad::class, 'IdAd', 'IdAd');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'IdUser');
    }
}