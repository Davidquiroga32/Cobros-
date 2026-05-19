<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function actual(Request $request)
    {
        $cobrador = $request->user();
        $caja = $cobrador->cajaHoy();

        if (! $caja) {
            return response()->json(['abierta' => false]);
        }

        $caja->sincronizarMontos();

        return response()->json([
            'abierta'         => true,
            'caja'            => $caja->fresh(),
            'monto_cobrado'   => $caja->monto_cobrado,
            'monto_inicial'   => $caja->monto_inicial,
            'monto_final'     => $caja->monto_final,
        ]);
    }

    public function abrir(Request $request)
    {
        $cobrador = $request->user();

        if ($cobrador->cajaHoy()) {
            return response()->json(['message' => 'Ya tienes una caja abierta hoy.'], 422);
        }

        $request->validate([
            'monto_inicial'  => ['required', 'numeric', 'min:0'],
            'notas_apertura' => ['nullable', 'string', 'max:500'],
        ]);

        $caja = Caja::create([
            'codigo'         => Caja::generarCodigo(),
            'cobrador_id'    => $cobrador->id,
            'sector_id'      => $cobrador->sector_id,
            'abierta_por'    => $cobrador->id,
            'monto_inicial'  => $request->monto_inicial,
            'notas_apertura' => $request->notas_apertura,
            'estado'         => 'abierta',
            'fecha_apertura' => now(),
            'fecha_jornada'  => today(),
        ]);

        return response()->json([
            'success' => true,
            'caja'    => $caja,
        ], 201);
    }

    public function cerrar(Request $request)
    {
        $cobrador = $request->user();
        $caja     = $cobrador->cajaHoy();

        if (! $caja) {
            return response()->json(['message' => 'No tienes una caja abierta hoy.'], 422);
        }

        $request->validate([
            'monto_gastos' => ['nullable', 'numeric', 'min:0'],
            'notas_cierre' => ['nullable', 'string', 'max:500'],
        ]);

        $caja->cerrar(
            cerradaPorId: $cobrador->id,
            gastos      : (float) $request->get('monto_gastos', 0),
            notas       : $request->get('notas_cierre'),
        );

        return response()->json([
            'success' => true,
            'caja'    => $caja->fresh(),
        ]);
    }
}
