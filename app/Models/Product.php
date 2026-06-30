<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table      = 'Products';
    protected $primaryKey = 'IdProduct';
    public    $timestamps = false;

    protected $fillable = [
        'IdUser',
        'IdCategory',
        'Name',
        'Description',
        'Price',
        'Stock',
        'ImageUrl',
        'Active',
        'CreatedAt',
    ];

    protected $casts = [
        'Price'     => 'float',
        'Stock'     => 'integer',
        'Active'    => 'boolean',
        'CreatedAt' => 'datetime',
    ];

    // Relationships

    public function owner()
    {
        return $this->belongsTo(User::class, 'IdUser', 'IdUser');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'IdCategory', 'IdCategory');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'TargetId', 'IdProduct')
            ->where('TargetType', 'product');
    }
}
