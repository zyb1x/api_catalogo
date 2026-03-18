<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Empleados extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'Empleados';
    protected $primaryKey = 'id_empleado';
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nombre',
        'apellido_p',
        'apellido_m',
        'correo',
        'usuario',
        'contrasena',
        'puesto',
        'area',
        'turno',
        'imagen'
    ];

    public function getAuthPassword()
    {
        return $this->contrasena;
    }


    public function getEmailForPasswordReset()
    {
        return $this->correo;
    }

    public function getRememberTokenName()
    {
        return null; // ← le dice a Laravel que no use remember_token
    }
}
