<?php
namespace App\Http\Controllers;

use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuariosController extends Controller
{
    public function index()
    {
        $usuarios = Usuarios::all();
        return response()->json($usuarios);
    }

    public function store(Request $request)
    {
        $request->validate([
            'correo' => 'required|email|unique:Usuarios,correo',
            'contrasena' => 'required|min:6',
        ]);

        

        
    }


}
?>