<?php

namespace App\Http\Controllers\Cobrador;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Credito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditoController extends Controller
{
    public function index(Request $request)
    {
        $cobrador = Auth::user();

        $query = Credito::with(['cliente', 'cuotas'])
            ->where('cobrador_id', $cobrador->id)
            ->withCount('cuotas');

        if ($search = $request->get('buscar')) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhereHas('cliente', fn ($cq) => $cq->where('nombre', 'like', "%{$search}%"));
            });
        }

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        $creditos = $query->latest()->paginate(15)->withQueryString();

        $totalActivos = Credito::where('cobrador_id', $cobrador->id)->whereIn('estado', ['activo', 'al_dia', 'mora'])->count();
        $enMora = Credito::where('cobrador_id', $cobrador->id)->where('estado', 'mora')->count();
        $carteraTotal = Credito::where('cobrador_id', $cobrador->id)->whereIn('estado', ['activo', 'al_dia', 'mora'])->sum('saldo_pendiente');
        $cobradoHoy = \App\Models\Pago::where('cobrador_id', $cobrador->id)->whereDate('fecha_pago', today())->sum('monto_pagado');

        return view('cobrador.creditos.index', compact(
            'creditos', 'totalActivos', 'enMora', 'carteraTotal', 'cobradoHoy'
        ));
    }

    public function create(Request $request)
    {
        $cobrador = Auth::user();
        $clientes = Cliente::where('cobrador_id', $cobrador->id)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $clienteSeleccionado = null;
        if ($clienteId = $request->get('cliente_id')) {
            $clienteSeleccionado = Cliente::where('cobrador_id', $cobrador->id)->find($clienteId);
        }

        return view('cobrador.creditos.create', compact('clientes', 'clienteSeleccionado'));
    }

    public function store(Request $request)
    {
        $cobrador = Auth::user();

        $validated = $request->validate([
            'cliente_id'     => ['required', 'exists:clientes,id'],
            'monto_prestado' => ['required', 'numeric', 'min:10000'],
            'tasa_interes'   => ['required', 'numeric', 'min:0', 'max:100'],
            'num_cuotas'     => ['required', 'integer', 'min:1', 'max:120'],
            'frecuencia'     => ['required', 'in:diaria,semanal,quincenal,mensual'],
            'fecha_inicio'   => ['required', 'date'],
            'notas'          => ['nullable', 'string', 'max:1000'],
        ], [
            'monto_prestado.min' => 'El monto minimo es $10,000.',
            'num_cuotas.min'     => 'Debe haber al menos 1 cuota.',
            'num_cuotas.max'     => 'Maximo 120 cuotas.',
        ]);

        abort_if(
            Cliente::where('id', $validated['cliente_id'])->where('cobrador_id', $cobrador->id)->doesntExist(),
            403
        );

        DB::transaction(function () use ($validated, $cobrador) {
            $monto      = (float) $validated['monto_prestado'];
            $tasa       = (float) $validated['tasa_interes'];
            $numCuotas  = (int) $validated['num_cuotas'];
            $totalPagar = $monto * (1 + $tasa / 100);
            $valorCuota = round($totalPagar / $numCuotas);
            $fechaInicio = \Carbon\Carbon::parse($validated['fecha_inicio']);

            $fechaVencimiento = match ($validated['frecuencia']) {
                'diaria'    => $fechaInicio->copy()->addDays($numCuotas),
                'semanal'   => $fechaInicio->copy()->addWeeks($numCuotas),
                'quincenal' => $fechaInicio->copy()->addDays($numCuotas * 15),
                'mensual'   => $fechaInicio->copy()->addMonths($numCuotas),
            };

            $credito = Credito::create([
                'codigo'             => Credito::generarCodigo(),
                'cliente_id'         => $validated['cliente_id'],
                'cobrador_id'        => $cobrador->id,
                'creado_por'         => $cobrador->id,
                'monto_prestado'     => $monto,
                'tasa_interes'       => $tasa,
                'num_cuotas'         => $numCuotas,
                'valor_cuota'        => $valorCuota,
                'total_a_pagar'      => $totalPagar,
                'saldo_pendiente'    => $totalPagar,
                'frecuencia'         => $validated['frecuencia'],
                'fecha_inicio'       => $fechaInicio,
                'fecha_vencimiento'  => $fechaVencimiento,
                'proxima_fecha_pago' => $fechaInicio->copy(),
                'estado'             => 'activo',
                'notas'              => $validated['notas'] ?? null,
            ]);

            $credito->generarCuotas();
        });

        return redirect()->route('cobrador.creditos.index')
            ->with('success', 'Credito creado exitosamente.');
    }

    public function show(Credito $credito)
    {
        abort_if($credito->cobrador_id !== Auth::id(), 403);

        $credito->load(['cliente', 'cuotas', 'pagos.cobrador']);

        return view('cobrador.creditos.show', compact('credito'));
    }
}
