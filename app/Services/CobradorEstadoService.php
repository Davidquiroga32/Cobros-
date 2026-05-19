<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Cuota;
use App\Models\Ruta;
use App\Models\User;
use Illuminate\Support\Collection;

class CobradorEstadoService
{
    /**
     * Dashboard de estado operativo con datos reales cruzados.
     *
     * Cruza:
     *  - Pagos reales del día agrupados por cobrador (1 query)
     *  - Cuotas pendientes del día como meta (1 query)
     *  - Progreso de ruta desde la tabla rutas
     *  - Datos de CobradorEstado (ubicación, conectado, etc.)
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function obtenerDashboard(): Collection
    {
        $hoy = today();

        $cobradores = User::with(['sector', 'estadoOperativo'])
            ->where('role', 'cobrador')
            ->where('active', true)
            ->get();

        if ($cobradores->isEmpty()) {
            return collect();
        }

        $cobradorIds = $cobradores->pluck('id')->toArray();

        $pagosHoy = Pago::selectRaw('cobrador_id, SUM(monto_pagado) as total_cobrado, COUNT(*) as total_pagos')
            ->whereIn('cobrador_id', $cobradorIds)
            ->whereDate('fecha_pago', $hoy)
            ->groupBy('cobrador_id')
            ->get()
            ->keyBy('cobrador_id');

        $cuotasPendientesHoy = Cuota::selectRaw('creditos.cobrador_id, COUNT(*) as pendientes, SUM(cuotas.saldo_cuota) as meta')
            ->join('creditos', 'cuotas.credito_id', '=', 'creditos.id')
            ->whereIn('creditos.cobrador_id', $cobradorIds)
            ->whereDate('cuotas.fecha_vencimiento', $hoy)
            ->whereIn('cuotas.estado', ['pendiente', 'parcial', 'vencida'])
            ->groupBy('creditos.cobrador_id')
            ->get()
            ->keyBy('cobrador_id');

        $rutasHoy = Ruta::whereIn('cobrador_id', $cobradorIds)
            ->whereDate('fecha', $hoy)
            ->get()
            ->keyBy('cobrador_id');

        $totalClientes = \App\Models\Cliente::selectRaw('cobrador_id, COUNT(*) as total')
            ->whereIn('cobrador_id', $cobradorIds)
            ->where('estado', 'activo')
            ->groupBy('cobrador_id')
            ->get()
            ->keyBy('cobrador_id');

        return $cobradores->map(function (User $cobrador) use (
            $pagosHoy, $cuotasPendientesHoy, $rutasHoy, $totalClientes
        ): array {
            $estadoOp   = $cobrador->estadoOperativo;
            $pagoData   = $pagosHoy->get($cobrador->id);
            $cuotaData  = $cuotasPendientesHoy->get($cobrador->id);
            $rutaData   = $rutasHoy->get($cobrador->id);
            $clienteData = $totalClientes->get($cobrador->id);

            $totalCobradoHoy = (float) ($pagoData->total_cobrado ?? 0);
            $totalPagosHoy   = (int) ($pagoData->total_pagos ?? 0);
            $cuotasPendientes = (int) ($cuotaData->pendientes ?? 0);
            $metaDia          = (float) ($cuotaData->meta ?? 0);
            $progresoRuta     = $rutaData ? $rutaData->porcentajeProgreso() : 0;

            $porcentajeMeta = 0;
            if ($metaDia > 0) {
                $porcentajeMeta = min(100, (int) round(($totalCobradoHoy / $metaDia) * 100));
            }

            return [
                'id'                => $cobrador->id,
                'nombre'            => $cobrador->name,
                'cn'                => $cobrador->cn ?? '—',
                'sector'            => $cobrador->sector?->nombre ?? 'Sin sector',
                'email'             => $cobrador->email,
                'telefono'          => $cobrador->phone ?? '—',
                'total_clientes'    => (int) ($clienteData->total ?? 0),

                'estado_operativo'  => $estadoOp->estado ?? 'offline',
                'conectado'         => (bool) ($estadoOp->conectado ?? false),
                'ultima_sync'       => $estadoOp?->ultima_sincronizacion
                    ? $estadoOp->ultima_sincronizacion->diffForHumans()
                    : '—',
                'ubicacion'         => $estadoOp->ubicacion_actual ?? null,
                'version_app'       => $estadoOp->version_app ?? null,

                'total_cobrado_hoy' => $totalCobradoHoy,
                'total_pagos_hoy'   => $totalPagosHoy,
                'meta_dia'          => $metaDia,
                'cuotas_pendientes' => $cuotasPendientes,
                'porcentaje_meta'   => $porcentajeMeta,

                'progreso_ruta'     => $progresoRuta,
                'caja_inicial'      => (float) ($estadoOp->caja_inicial ?? 0),
            ];
        });
    }
}
