<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    public $timestamps = false;
    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';


    protected $fillable = [
        'nombre_categoria',
        'descripcion',
    ];
}
