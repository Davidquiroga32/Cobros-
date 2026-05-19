<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Credito;
use Illuminate\Http\Request;

class CreditoController extends Controller
{
    public function index(Request $request)
    {
        $cobrador = $request->user();

        $query = Credito::with(['cliente', 'cuotas'])
            ->where('cobrador_id', $cobrador->id)
            ->withCount('cuotas');

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        $creditos = $query->latest()->paginate(15);

        return response()->json($creditos);
    }

    public function show(Credito $credito, Request $request)
    {
        if ($credito->cobrador_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $credito->load(['cliente', 'cuotas', 'pagos.cobrador']);

        return response()->json([
            'credito'         => $credito,
            'porcentaje_pagado' => $credito->porcentajePagado(),
            'cuotas_pagadas'    => $credito->cuotasPagadas(),
            'cuotas_pendientes' => $credito->cuotasPendientes(),
        ]);
    }
}
