<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'Category'; // ou 'categories' selon ta DB

    protected $primaryKey = 'IdCateg';

    public $timestamps = false; // si ta table n'a pas created_at / updated_at

    protected $fillable = [
        'TitleEn',
        'TitleFr',
        'TitleAr',
        'Description',
        'Image',
        'Idtypecat',
        'Active'
    ];
    //
}
