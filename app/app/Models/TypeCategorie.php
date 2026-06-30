<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeCategorie  extends Model
{
    protected $table = 'type_categorie';
    // protected $table = 'type_categorie';
    protected $primaryKey = 'Idtypecat';
    public $timestamps = false;

    protected $fillable = [
        'Title',
        'Description',
        'Active'
    ];
    
}
