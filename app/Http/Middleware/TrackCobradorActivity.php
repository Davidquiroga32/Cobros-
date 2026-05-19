<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackCobradorActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        if (! $user || ! $user->isCobrador()) {
            return $response;
        }

        \App\Models\CobradorEstado::updateOrCreate(
            ['cobrador_id' => $user->id],
            [
                'cn'                    => $user->cn,
                'conectado'             => true,
                'ultima_sincronizacion' => now(),
                'estado'                => 'en_ruta',
                'ubicacion_actual'      => $user->sector?->nombre ?? 'Sin sector',
                'version_app'           => 'web',
            ]
        );

        return $response;
    }
}
