<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuarios extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'Usuarios';
    // protected $primaryKey = 'id_usuario'; se cambio en la base de datos a id
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
   
    protected $fillable = [
        'nombre',
        'correo',
        'usuario',
        'contrasena',
        'rol',
        'turno',
        'imagen',
    ];

    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    
    public function getEmailForPasswordReset()
    {
        return $this->correo;
    }
  

    
   
}
