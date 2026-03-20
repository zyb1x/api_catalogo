<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Login extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // protected $table = 'Usuarios';

    // public $timestamps = false;
   
    protected $fillable = [
        'name',
        'email',
        'password',
        'imagen',
    ];

     protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }
}

?>