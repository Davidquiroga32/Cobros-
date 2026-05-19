<?php

namespace App\Http\Controllers\Cobrador;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CajaController extends Controller
{
    public function index()
    {
        $cobrador = Auth::user();

        $cajaHoy = $cobrador->cajaHoy();

        $historial = Caja::where('cobrador_id', $cobrador->id)
            ->with('sector')
            ->latest('fecha_jornada')
            ->take(30)
            ->get();

        return view('cobrador.caja.index', compact('cajaHoy', 'historial'));
    }

    public function abrir(Request $request)
    {
        $cobrador = Auth::user();

        // No puede abrir dos cajas el mismo día
        if ($cobrador->cajaHoy()) {
            return back()->with('error', '❌ Ya tienes una caja abierta hoy.');
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

        return redirect()->route('cobrador.caja.index')
            ->with('success', "✅ Caja abierta con $" . number_format($caja->monto_inicial, 0, ',', '.'));
    }

    public function cerrar(Request $request)
    {
        $cobrador = Auth::user();
        $caja     = $cobrador->cajaHoy();

        if (! $caja) {
            return back()->with('error', '❌ No tienes una caja abierta hoy.');
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

        return redirect()->route('cobrador.caja.index')
            ->with('success', "✅ Caja cerrada correctamente. Total cobrado: $" . number_format($caja->fresh()->monto_cobrado, 0, ',', '.'));
    }
}