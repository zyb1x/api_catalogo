<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestamos extends Model
{
    protected $table      = 'prestamos';
    protected $primaryKey = 'id_prestamo';
    public    $timestamps = false; // fecha_prestamo se maneja con default en BD

    protected $fillable = [
        'id_usuario',
        'id_empleado',
        'estatus_general',
        'total',
    ];

    // Un préstamo tiene muchos detalles
    public function detalles()
    {
        return $this->hasMany(DetallePrestamo::class, 'id_prestamo', 'id_prestamo');
    }
}
