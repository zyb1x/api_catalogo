<?php

namespace App\Http\Controllers;

use App\Models\Prestamos;
use App\Models\DetallePrestamo;
use App\Models\Herramientas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrestamosController extends Controller
{
    // GET /api/prestamos
    public function index(Request $request)
    {
        $prestamos = Prestamos::with('detalles.herramienta')
            ->where('id_usuario', $request->user()->id)
            ->orderByDesc('fecha_prestamo')
            ->get();

        $prestamos->each(function ($prestamo) {
            $prestamo->total = $prestamo->detalles->sum('subtotal');
        });

        return response()->json([
            'resultado' => true,
            'datos'     => $prestamos,
        ], 200);
    }

    // GET /api/prestamos/{id}
    public function show(Request $request, $id)
    {
        $prestamo = Prestamos::with('detalles.herramienta')
            ->where('id_prestamo', $id)
            ->where('id_usuario', $request->user()->id)
            ->first();

        if (!$prestamo) {
            return response()->json([
                'resultado' => false,
                'datos'     => '',
                'error'     => 'Pedido no encontrado.',
            ], 404);
        }

        $prestamo->total = $prestamo->detalles->sum('subtotal');

        return response()->json([
            'resultado' => true,
            'datos'     => $prestamo,
        ], 200);
    }

    // POST /api/prestamos
    // Body: { items: [{id_herramienta, cantidad}] }
    public function store(Request $request)
    {
        $validar = Validator::make($request->all(), [
            'items'                    => 'required|array|min:1',
            'items.*.id_herramienta'   => 'required|integer|exists:herramientas,id_herramienta',
            'items.*.cantidad'         => 'required|integer|min:1',
        ], [
            'items.required'                  => 'El pedido debe tener al menos un artículo.',
            'items.*.id_herramienta.required' => 'Cada ítem debe tener una herramienta.',
            'items.*.id_herramienta.exists'   => 'Una herramienta del pedido no existe.',
            'items.*.cantidad.required'       => 'Cada ítem debe tener una cantidad.',
            'items.*.cantidad.min'            => 'La cantidad mínima es 1.',
        ]);

        if ($validar->fails()) {
            return response()->json([
                'resultado' => false,
                'datos'     => '',
                'error'     => $validar->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Verificar existencias antes de tocar nada
            foreach ($request->items as $item) {
                $herramienta = Herramientas::find($item['id_herramienta']);

                if (!$herramienta || !$herramienta->disponible) {
                    DB::rollBack();
                    return response()->json([
                        'resultado' => false,
                        'datos'     => '',
                        'error'     => "La herramienta '{$herramienta->nombre_herramienta}' no está disponible.",
                    ], 422);
                }

                if ($herramienta->existencia < $item['cantidad']) {
                    DB::rollBack();
                    return response()->json([
                        'resultado' => false,
                        'datos'     => '',
                        'error'     => "Stock insuficiente para '{$herramienta->nombre_herramienta}'. Disponible: {$herramienta->existencia}.",
                    ], 422);
                }
            }

            // Crear préstamo sin id_empleado
            $prestamo = Prestamos::create([
                'id_usuario'      => $request->user()->id,
                'estatus_general' => 'Activo',
                'total'           => 0,
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $herramienta = Herramientas::find($item['id_herramienta']);
                $subtotal    = ($herramienta->precio ?? 0) * $item['cantidad'];
                $total      += $subtotal;

                DetallePrestamo::create([
                    'id_prestamo'      => $prestamo->id_prestamo,
                    'id_herramienta'   => $herramienta->id_herramienta,
                    'cantidad'         => $item['cantidad'],
                    'estatus_articulo' => 'Prestado',
                    'subtotal'         => $subtotal,
                ]);

                $herramienta->existencia -= $item['cantidad'];
                if ($herramienta->existencia <= 0) {
                    $herramienta->disponible = 0;
                }
                $herramienta->save();
            }

            $prestamo->total = $total;
            $prestamo->save();

            DB::commit();

            return response()->json([
                'resultado' => true,
                'datos'     => $prestamo->id_prestamo,
                'error'     => '',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'resultado' => false,
                'datos'     => '',
                'error'     => 'Error al crear el pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    // PATCH /api/prestamos/{id}/cancelar
    public function cancelar(Request $request, $id)
    {
        $prestamo = Prestamos::with('detalles.herramienta')
            ->where('id_prestamo', $id)
            ->where('id_usuario', $request->user()->id)
            ->first();

        if (!$prestamo) {
            return response()->json([
                'resultado' => false,
                'datos'     => '',
                'error'     => 'Pedido no encontrado.',
            ], 404);
        }

        if ($prestamo->estatus_general === 'Cerrado') {
            return response()->json([
                'resultado' => false,
                'datos'     => '',
                'error'     => 'No se puede cancelar un pedido cerrado.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($prestamo->detalles as $detalle) {
                if ($detalle->herramienta) {
                    $detalle->herramienta->existencia += $detalle->cantidad;
                    $detalle->herramienta->disponible  = 1;
                    $detalle->herramienta->save();
                }
            }

            $prestamo->estatus_general = 'Cerrado';
            $prestamo->save();

            DB::commit();

            return response()->json([
                'resultado' => true,
                'datos'     => '',
                'error'     => '',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'resultado' => false,
                'datos'     => '',
                'error'     => 'Error al cancelar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
