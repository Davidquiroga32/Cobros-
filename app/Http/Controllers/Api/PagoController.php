<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function hoy(Request $request)
    {
        $cobrador = $request->user();

        $pagos = Pago::with(['cliente', 'cuota', 'credito'])
            ->where('cobrador_id', $cobrador->id)
            ->whereDate('fecha_pago', today())
            ->latest('fecha_pago')
            ->get();

        return response()->json([
            'total_cobrado' => $pagos->sum('monto_pagado'),
            'total_pagos'   => $pagos->count(),
            'pagos'         => $pagos,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cuota_id'       => ['required', 'exists:cuotas,id'],
            'monto_pagado'   => ['required', 'numeric', 'min:0.01'],
            'metodo_pago'    => ['required', 'in:efectivo,transferencia,nequi,daviplata,otro'],
            'observaciones'  => ['nullable', 'string', 'max:500'],
            'latitud'        => ['nullable', 'numeric'],
            'longitud'       => ['nullable', 'numeric'],
            'comprobante_foto' => ['nullable', 'string'],
        ]);

        $cobrador = $request->user();
        $cuota = Cuota::with('credito')->findOrFail($request->cuota_id);

        if ($cuota->credito->cobrador_id !== $cobrador->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($request->monto_pagado > $cuota->saldo_cuota) {
            return response()->json(['message' => 'El monto supera el saldo de la cuota'], 422);
        }

        DB::transaction(function () use ($request, $cobrador, $cuota) {
            $credito  = $cuota->credito;
            $monto    = (float) $request->monto_pagado;
            $esParcial = $monto < $cuota->saldo_cuota;

            Pago::create([
                'recibo_numero'  => Pago::generarRecibo(),
                'credito_id'     => $credito->id,
                'cuota_id'       => $cuota->id,
                'cliente_id'     => $cuota->cliente_id,
                'cobrador_id'    => $cobrador->id,
                'monto_pagado'   => $monto,
                'monto_mora'     => 0,
                'total_recibido' => $monto,
                'metodo_pago'    => $request->metodo_pago,
                'fecha_pago'     => now(),
                'observaciones'  => $request->observaciones,
                'latitud'        => $request->latitud,
                'longitud'       => $request->longitud,
                'comprobante_foto' => $request->comprobante_foto,
                'es_pago_parcial'  => $esParcial,
            ]);

            $nuevoSaldo  = $cuota->saldo_cuota - $monto;
            $nuevoPagado = $cuota->valor_pagado + $monto;
            $cuota->update([
                'valor_pagado' => $nuevoPagado,
                'saldo_cuota'  => $nuevoSaldo,
                'fecha_pago'   => $esParcial ? null : now()->toDateString(),
                'estado'       => $esParcial ? 'parcial' : 'pagada',
            ]);

            $nuevoSaldoCredito = $credito->saldo_pendiente - $monto;
            $estadoCredito = $nuevoSaldoCredito <= 0
                ? 'pagado'
                : ($credito->cuotas()->where('estado', 'vencida')->exists() ? 'mora' : 'al_dia');

            $credito->update([
                'saldo_pendiente'    => max(0, $nuevoSaldoCredito),
                'estado'             => $estadoCredito,
                'proxima_fecha_pago' => $credito->cuotas()
                    ->whereIn('estado', ['pendiente', 'parcial'])
                    ->orderBy('fecha_vencimiento')
                    ->value('fecha_vencimiento'),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado exitosamente.',
        ], 201);
    }
}
