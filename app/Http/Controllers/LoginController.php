<?php

namespace App\Http\Controllers;

// use App\Models\Empleados;
use Illuminate\Http\Request;
use App\Models\Usuarios;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class LoginController extends Controller
{

    public function create()
    {
        return view('login.formulario-crear');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'nombre' => 'required',
                'correo' => 'required|email|unique:Usuarios,correo',
                'usuario' => 'required|unique:Usuarios,usuario,correo',
                'contrasena' => 'required|min:6',
                'conf_contrasena' => 'required|same:contrasena',
                'rol'   => 'required|in:Administrador,Almacenista',
                'turno' => 'required|in:Matutino,Vespertino,Nocturno',
                'imagen' => 'required|nullable|max:2048|mimes:jpeg,png,jpg'
            ],
            [
                'nombre.required' => 'El nombre es obligatorio.',
                'usuario.required' => 'El nombre de usuario es obligatorio.',
                'usuario.unique' => 'Ya existe un empleado con ese nombre de usuario.',
                'correo.required' => 'El correo electrónico es obligatorio.',
                'correo.email' => 'El correo electrónico debe ser una dirección válida.',
                'correo.unique' => 'Ya existe un empleado con ese correo electrónico.',
                'contrasena.required' => 'La contraseña es obligatoria.',
                'contrasena.min' => 'La contraseña debe tener al menos 6 caracteres.',
                'conf_contrasena.required' => 'La confirmación de la contraseña es obligatoria.',
                'conf_contrasena.same' => 'Las contraseñas no coinciden.',
                'rol.required' => 'El rol es obligatorio.',
                'rol.in'       => 'Debes seleccionar un rol válido.',
                'turno.required' => 'El turno es obligatorio.',
                'turno.in'       => 'Debes seleccionar un turno válido.',
                'imagen.required' => 'La imagen es obligatoria.',
                'imagen.max' => 'La imagen no debe superar los 2MB.',
                'imagen.mimes' => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg.'
            ]
        );

        $usuario = new Usuarios();
        $usuario->nombre = $request->nombre;
        $usuario->usuario = $request->usuario;
        $usuario->correo = $request->correo;
        $usuario->contrasena = Hash::make($request->contrasena);
        $usuario->rol = $request->rol;
        $usuario->turno = $request->turno;
        $usuario->imagen = $request->imagen;

        $usuario->save();

        if ($request->has('imagen')) {
            $imagen = $request->imagen;
            $nuevo_nombre = 'usuario_' . $usuario->id . '.jpg';
            $ruta = $imagen->storeAs('imagenes/usuarios', $nuevo_nombre, 'public');
            $usuario->imagen = '/storage/' . $ruta;
            $usuario->save();
        }



        return redirect()->route('login')->with('success', 'Usuario creado exitosamente.');
    }

    public function showLoginForm()
    {
        return view('login.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $usuario = Usuarios::where('correo', $request->email)->first();

        if (!$usuario) {
            return back()->withErrors([
                'error' => 'No existe una cuenta con ese correo electrónico.'
            ])->withInput();
        }

        if (!Hash::check($request->password, $usuario->contrasena)) {
            return back()->withErrors([
                'error' => 'La contraseña es incorrecta.'
            ])->withInput();
        }

        Auth::guard('usuarios')->login($usuario);
        $request->session()->regenerate();

        return redirect('/inicio');
    }

    public function logout(Request $request)
    {
        Auth::guard('usuarios')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
