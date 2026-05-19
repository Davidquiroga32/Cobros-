<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CobradorEstado;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CobradorEstadoApiController extends Controller
{
    /**
     * Sincroniza el estado operativo del cobrador.
     *
     * SEGURIDAD: Esta ruta DEBE estar protegida con middleware auth:sanctum
     * o auth:api. En el estado original no tenía ningún middleware,
     * lo que permitía a cualquiera actualizar el estado de cualquier cobrador.
     */
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'cobrador_id'           => ['required', 'exists:users,id'],
            'estado'                => ['required', 'in:disponible,en_ruta,pausado,sincronizando,offline'],
            'ubicacion_actual'      => ['nullable', 'string', 'max:255'],
            'latitud'               => ['nullable', 'numeric', 'between:-90,90'],
            'longitud'              => ['nullable', 'numeric', 'between:-180,180'],
            'conectado'             => ['boolean'],
            'version_app'           => ['nullable', 'string', 'max:20'],
            'caja_inicial'          => ['nullable', 'numeric', 'min:0'],
            'caja_final'            => ['nullable', 'numeric', 'min:0'],
        ]);

        // SEGURIDAD: Cobrador solo puede actualizar su propio estado
        // Si la app envía su propio token, verificar que coincide
        $cobrador = Auth::user();
        if ($cobrador && $cobrador->id !== (int) $validated['cobrador_id']) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $estado = CobradorEstado::updateOrCreate(
            ['cobrador_id' => $validated['cobrador_id']],
            array_merge($validated, [
                'ultima_sincronizacion' => now(),
                'conectado'             => $validated['conectado'] ?? true,
            ])
        );

        // Actualizar progreso de ruta en el estado operativo
        $ruta = Ruta::where('cobrador_id', $validated['cobrador_id'])
            ->whereDate('fecha', today())
            ->first();

        if ($ruta) {
            $estado->update(['progreso_ruta' => $ruta->porcentajeProgreso()]);
        }

        return response()->json([
            'success' => true,
            'data'    => $estado->fresh(),
        ]);
    }
}