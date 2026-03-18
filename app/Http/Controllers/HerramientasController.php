<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Herramientas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HerramientasController extends Controller
{
    // GET /api/herramientas
    public function index()
    {
        $herramientas = Herramientas::with('categoria')->get();
        return response()->json([
            'resultado' => true,
            'datos'     => $herramientas
        ], 200);
    }

    // GET /api/herramientas/{id}
    public function show($id)
    {
        $validar = Validator::make(
            ['id' => $id],
            ['id' => 'required|integer|exists:herramientas,id_herramienta'],
            [
                'id.required' => 'El ID es obligatorio.',
                'id.integer'  => 'El ID debe ser un número entero.',
                'id.exists'   => 'No se encontró una herramienta con ese ID.'
            ]
        );

        if ($validar->fails()) {
            return response()->json([
                'resultado' => false,
                'datos'     => '',
                'error'     => $validar->errors()
            ], 422);
        }

        $herramienta = Herramientas::with('categoria')->find($id);
        return response()->json([
            'resultado' => true,
            'datos'     => $herramienta
        ], 200);
    }

    // POST /api/herramientas
    public function store(Request $request)
    {
        $validar = Validator::make($request->all(), [
            'id_categoria'       => 'required|integer|exists:categorias,id_categoria',
            'nombre_herramienta' => 'required|string|max:150',
            'existencia'         => 'required|integer|min:0',
            'imagen'             => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'id_categoria.required'       => 'La categoría es obligatoria.',
            'id_categoria.exists'         => 'La categoría seleccionada no existe.',
            'nombre_herramienta.required' => 'El nombre de la herramienta es obligatorio.',
            'existencia.required'         => 'La existencia es obligatoria.',
            'existencia.integer'          => 'La existencia debe ser un número entero.',
            'existencia.min'              => 'La existencia no puede ser negativa.',
            'imagen.required'             => 'La imagen es obligatoria.',
            'imagen.image'                => 'El archivo debe ser una imagen.',
            'imagen.mimes'                => 'La imagen debe ser de tipo: jpeg, png, jpg.',
            'imagen.max'                  => 'La imagen no debe superar los 2MB.',
        ]);

        if ($validar->fails()) {
            return response()->json([
                'resultado' => false,
                'datos'     => '',
                'error'     => $validar->errors()
            ], 422);
        }

        $herramienta                    = new Herramientas();
        $herramienta->id_categoria      = $request->id_categoria;
        $herramienta->nombre_herramienta = $request->nombre_herramienta;
        $herramienta->existencia        = $request->existencia;
        $herramienta->estado            = 'Buen Estado';
        $herramienta->disponible        = 1;
        $herramienta->imagen            = '/imagenes/herramientas/producto_default.png';

        $herramienta->save();

        if ($request->hasFile('imagen')) {
            $nuevo_nombre = 'herramienta_' . $herramienta->id_herramienta . '.jpg';
            $ruta = $request->file('imagen')->storeAs('imagenes/herramientas', $nuevo_nombre, 'public');
            $herramienta->imagen = '/storage/' . $ruta;
            $herramienta->save();
        }

        return response()->json([
            'resultado' => true,
            'datos'     => $herramienta->id_herramienta,
            'error'     => ''
        ], 201);
    }

    // PUT /api/herramientas/{id}
    public function update(Request $request, $id)
    {
        $herramienta = Herramientas::find($id);

        if (!$herramienta) {
            return response()->json([
                'resultado' => false,
                'datos'     => '',
                'error'     => 'Herramienta no encontrada.'
            ], 404);
        }

        $validar = Validator::make($request->all(), [
            'id_categoria'       => 'required|integer|exists:categorias,id_categoria',
            'nombre_herramienta' => 'required|string|max:150',
            'existencia'         => 'required|integer|min:0',
            'imagen'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'eliminar_imagen'    => 'nullable|in:0,1',
        ], [
            'id_categoria.required'       => 'La categoría es obligatoria.',
            'id_categoria.exists'         => 'La categoría seleccionada no existe.',
            'nombre_herramienta.required' => 'El nombre de la herramienta es obligatorio.',
            'existencia.required'         => 'La existencia es obligatoria.',
            'existencia.integer'          => 'La existencia debe ser un número entero.',
            'existencia.min'              => 'La existencia no puede ser negativa.',
            'imagen.image'                => 'El archivo debe ser una imagen.',
            'imagen.mimes'                => 'La imagen debe ser de tipo: jpeg, png, jpg.',
            'imagen.max'                  => 'La imagen no debe superar los 2MB.',
        ]);

        if ($validar->fails()) {
            return response()->json([
                'resultado' => false,
                'datos'     => '',
                'error'     => $validar->errors()
            ], 422);
        }

        $herramienta->id_categoria       = $request->id_categoria;
        $herramienta->nombre_herramienta = $request->nombre_herramienta;
        $herramienta->existencia         = $request->existencia;

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si no es la default
            if ($herramienta->imagen && !str_contains($herramienta->imagen, 'producto_default')) {
                $ruta_anterior = str_replace('/storage/', '', $herramienta->imagen);
                Storage::disk('public')->delete($ruta_anterior);
            }
            $nuevo_nombre = 'herramienta_' . $id . '.jpg';
            $ruta = $request->file('imagen')->storeAs('imagenes/herramientas', $nuevo_nombre, 'public');
            $herramienta->imagen = '/storage/' . $ruta;
        } elseif ($request->input('eliminar_imagen') == '1') {
            // Restaurar imagen a la default
            if ($herramienta->imagen && !str_contains($herramienta->imagen, 'producto_default')) {
                $ruta_anterior = str_replace('/storage/', '', $herramienta->imagen);
                Storage::disk('public')->delete($ruta_anterior);
            }
            $herramienta->imagen = '/imagenes/herramientas/producto_default.png';
        }

        $herramienta->save();

        return response()->json([
            'resultado' => true,
            'datos'     => $herramienta,
            'error'     => ''
        ], 200);
    }

    // DELETE /api/herramientas/{id}
    public function destroy($id)
    {
        $validar = Validator::make(
            ['id' => $id],
            ['id' => 'required|integer|exists:herramientas,id_herramienta'],
            [
                'id.required' => 'El ID es obligatorio.',
                'id.integer'  => 'El ID debe ser un número entero.',
                'id.exists'   => 'No se encontró una herramienta con ese ID.'
            ]
        );

        if ($validar->fails()) {
            return response()->json([
                'resultado' => false,
                'datos'     => '',
                'error'     => $validar->errors()
            ], 422);
        }

        $herramienta = Herramientas::find($id);
        $herramienta->update(['disponible' => 0]);

        return response()->json([
            'resultado' => true,
            'datos'     => '',
            'error'     => ''
        ], 200);
    }
}
