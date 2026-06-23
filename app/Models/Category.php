<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $primaryKey = 'IdCateg';

    public $timestamps = false;

    protected $fillable = [
        'TitleEn',
        'TitleFr',
        'TitleAr',
        'Description',
        'Image',
        'idtypecat',
        'Active'
    ];

    public function type()
    {
        return $this->belongsTo(TypeCategorie::class, 'idtypecat', 'Idtypecat');
    }
}