<?php

namespace App\Http\Controllers\Cobrador;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use App\Models\RutaParada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RutaController extends Controller
{
    /**
     * Muestra la ruta del día del cobrador (o la genera si no existe).
     */
    public function index()
    {
        $cobrador = Auth::user();
        $caja     = $cobrador->cajaHoy();

        // Generar automáticamente si no existe
        $ruta = Ruta::generarDesde($cobrador->id, $caja?->id);
        $ruta->load(['paradas.cliente', 'paradas.cuota']);

        $progreso = $ruta->porcentajeProgreso();

        $paradasPendientes  = $ruta->paradas->where('estado', 'pendiente');
        $paradasCompletadas = $ruta->paradas->where('estado', 'visitado');
        $paradasNoEncontradas = $ruta->paradas->where('estado', 'no_encontrado');

        return view('cobrador.ruta.index', compact(
            'ruta', 'progreso', 'caja',
            'paradasPendientes', 'paradasCompletadas', 'paradasNoEncontradas'
        ));
    }

    /**
     * Marcar una parada como visitada / no encontrada.
     */
    public function actualizarParada(Request $request, RutaParada $parada)
    {
        // Seguridad: solo el dueño de la ruta puede actualizar
        abort_if($parada->ruta->cobrador_id !== Auth::id(), 403);

        $request->validate([
            'estado'        => ['required', 'in:visitado,no_encontrado,reagendado'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        if ($request->estado === 'visitado') {
            $parada->marcarVisitado(
                montoCobrado: (float) $request->get('monto_cobrado', 0),
                obs         : $request->get('observaciones'),
            );
        } else {
            $parada->update([
                'estado'        => $request->estado,
                'hora_visita'   => now(),
                'observaciones' => $request->get('observaciones'),
            ]);
            $parada->ruta->recalcularProgreso();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success'   => true,
                'progreso'  => $parada->ruta->fresh()->porcentajeProgreso(),
            ]);
        }

        return back()->with('success', '✅ Parada actualizada.');
    }

    /**
     * Reordenar paradas via drag-and-drop (AJAX).
     */
    public function reordenar(Request $request, Ruta $ruta)
    {
        abort_if($ruta->cobrador_id !== Auth::id(), 403);

        $request->validate([
            'orden' => ['required', 'array'],
            'orden.*' => ['integer', 'exists:ruta_paradas,id'],
        ]);

        foreach ($request->orden as $posicion => $paradaId) {
            RutaParada::where('id', $paradaId)
                ->where('ruta_id', $ruta->id) // Seguridad extra
                ->update(['orden' => $posicion + 1]);
        }

        return response()->json(['success' => true]);
    }
}