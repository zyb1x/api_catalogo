<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePrestamo extends Model
{
    protected $table      = 'detalle_prestamos';
    protected $primaryKey = 'id_detalle';
    public    $timestamps = false;

    protected $fillable = [
        'id_prestamo',
        'id_herramienta',
        'id_material',
        'cantidad',
        'fecha_devolucion_esperada',
        'fecha_devolucion_real',
        'estatus_articulo',
        'subtotal',
    ];

    // Pertenece a un préstamo
    public function prestamo()
    {
        return $this->belongsTo(Prestamos::class, 'id_prestamo', 'id_prestamo');
    }

    // Pertenece a una herramienta
    public function herramienta()
    {
        return $this->belongsTo(Herramientas::class, 'id_herramienta', 'id_herramienta');
    }
}
