<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Prestamos;

class PaypalController extends Controller
{
    private function getProvider()
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        return $provider;
    }

    public function pago(Request $request)
    {
        $idPrestamo = $request->input('id_prestamo');
        $total      = number_format((float) $request->input('total', 0), 2, '.', '');
        $clienteUrl = config('app.cliente_url', 'http://127.0.0.1:8001');

        try {
            $provider = $this->getProvider();

            $orden = $provider->createOrder([
                'intent' => 'CAPTURE',
                'application_context' => [
                    'return_url' => "{$clienteUrl}/paypal/exitoso?id_prestamo={$idPrestamo}",
                    'cancel_url' => "{$clienteUrl}/paypal/cancelado?id_prestamo={$idPrestamo}",
                    'brand_name' => 'Herramientas',
                    'locale'     => 'es-MX',
                    'user_action' => 'PAY_NOW',
                ],
                'purchase_units' => [[
                    'reference_id' => "pedido-{$idPrestamo}",
                    'description'  => "Pago de pedido #{$idPrestamo}",
                    'amount'       => [
                        'currency_code' => 'MXN',
                        'value'         => $total,
                    ],
                ]],
            ]);

            if (isset($orden['id']) && $orden['status'] === 'CREATED') {
                foreach ($orden['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return response()->json([
                            'resultado'    => true,
                            'approval_url' => $link['href'],
                            'order_id'     => $orden['id'],
                        ]);
                    }
                }
            }

            return response()->json([
                'resultado' => false,
                'error'     => 'No se pudo crear la orden en PayPal.',
                'detalle'   => $orden ?? [],
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'resultado' => false,
                'error'     => $e->getMessage(),
            ], 500);
        }
    }

    public function procesarPago(Request $request)
    {
        $orderId    = $request->input('order_id');
        $idPrestamo = $request->input('id_prestamo');

        try {
            $provider  = $this->getProvider();
            $resultado = $provider->capturePaymentOrder($orderId);

            if (isset($resultado['status']) && $resultado['status'] === 'COMPLETED') {
                $transaccionId = $resultado['purchase_units'][0]['payments']['captures'][0]['id'] ?? $orderId;

                $prestamo = Prestamos::find($idPrestamo);
                if ($prestamo) {
                    $prestamo->transaccion_id = $transaccionId;
                    $prestamo->estado_pago    = 'COMPLETADO';
                    $prestamo->fecha_pago     = now();
                    $prestamo->save();
                }

                return response()->json([
                    'resultado'      => true,
                    'transaccion_id' => $transaccionId,
                    'estado'         => 'COMPLETADO',
                ]);
            }

            return response()->json([
                'resultado' => false,
                'error'     => 'El pago no pudo completarse.',
                'detalle'   => $resultado,
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'resultado' => false,
                'error'     => $e->getMessage(),
            ], 500);
        }
    }

    public function cancelarPago(Request $request)
    {
        return response()->json([
            'resultado' => false,
            'mensaje'   => 'Pago cancelado por el usuario.',
        ]);
    }
}
