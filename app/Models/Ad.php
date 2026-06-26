<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $table = 'ads';
    protected $primaryKey = 'IdAd';
    protected $fillable = [
        'TitleAd',
        'DescriptionAd',
        'DetailsAd',
        'PriceAd',
        'ImageAd',
        'IdCateg',
        'IdUser',
        'Type',
        'Active',
        'views',
        'LocationAd',
        'Color',
        'Brand',
        'Telephone',
        'Email'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'IdCateg', 'IdCateg');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'IdUser');
    }

    public function likes()
    {
        return $this->hasMany(AdLike::class, 'IdAd', 'IdAd');
    }
}