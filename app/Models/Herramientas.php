<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Herramientas extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'herramientas';
    protected $primaryKey = 'id_herramienta';

    protected $fillable = [
        'id_categoria',
        'nombre_herramienta',
        'existencia',
        'estado',
        'disponible',
        'precio',
        'imagen',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }
}
