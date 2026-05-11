<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditoController extends Controller
{
    public function index(Request $request)
    {
        $query = Credito::with(['cliente', 'cobrador'])
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

        if ($cobrador = $request->get('cobrador_id')) {
            $query->where('cobrador_id', $cobrador);
        }

        $creditos   = $query->latest()->paginate(20)->withQueryString();
        $cobradores = User::where('role', 'cobrador')->orderBy('name')->get();

        $totalCreditos  = Credito::count();
        $creditosActivos = Credito::whereIn('estado', ['activo', 'al_dia', 'mora'])->count();
        $creditosMora   = Credito::where('estado', 'mora')->count();
        $carteraTotal   = Credito::whereIn('estado', ['activo', 'al_dia', 'mora'])->sum('saldo_pendiente');

        return view('admin.creditos.index', compact(
            'creditos', 'cobradores', 'totalCreditos', 'creditosActivos', 'creditosMora', 'carteraTotal'
        ));
    }

    public function create(Request $request)
    {
        $cobradores = User::where('role', 'cobrador')->where('active', true)->orderBy('name')->get();
        $clientes   = collect();

        // Si viene pre-seleccionado un cliente
        $clienteSeleccionado = null;
        if ($clienteId = $request->get('cliente_id')) {
            $clienteSeleccionado = Cliente::find($clienteId);
            if ($clienteSeleccionado) {
                $clientes = Cliente::where('cobrador_id', $clienteSeleccionado->cobrador_id)
                    ->where('estado', 'activo')
                    ->orderBy('nombre')
                    ->get();
            }
        }

        return view('admin.creditos.create', compact('cobradores', 'clientes', 'clienteSeleccionado'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'     => ['required', 'exists:clientes,id'],
            'cobrador_id'    => ['required', 'exists:users,id'],
            'monto_prestado' => ['required', 'numeric', 'min:10000'],
            'tasa_interes'   => ['required', 'numeric', 'min:0', 'max:100'],
            'num_cuotas'     => ['required', 'integer', 'min:1', 'max:120'],
            'frecuencia'     => ['required', 'in:diaria,semanal,quincenal,mensual'],
            'fecha_inicio'   => ['required', 'date'],
            'notas'          => ['nullable', 'string', 'max:1000'],
        ], [
            'monto_prestado.min' => 'El monto mínimo es $10,000.',
            'num_cuotas.min'     => 'Debe haber al menos 1 cuota.',
            'num_cuotas.max'     => 'Máximo 120 cuotas.',
        ]);

        DB::transaction(function () use ($validated, $request) {
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
                'cobrador_id'        => $validated['cobrador_id'],
                'creado_por'         => auth()->id(),
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

        return redirect()->route('admin.creditos.index')
            ->with('success', '✅ Crédito creado y cuotas generadas exitosamente.');
    }

    public function show(Credito $credito)
    {
        $credito->load(['cliente', 'cobrador', 'cuotas', 'pagos.cobrador']);
        return view('admin.creditos.show', compact('credito'));
    }

    public function destroy(Credito $credito)
    {
        if ($credito->pagos()->exists()) {
            return back()->with('error', 'No se puede eliminar un crédito con pagos registrados.');
        }
        $credito->cuotas()->delete();
        $credito->delete();
        return redirect()->route('admin.creditos.index')
            ->with('success', 'Crédito eliminado.');
    }

    /**
     * API: Retorna clientes de un cobrador (para AJAX en formulario)
     */
    public function clientesPorCobrador(User $cobrador)
    {
        $clientes = Cliente::where('cobrador_id', $cobrador->id)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'cedula', 'telefono']);

        return response()->json($clientes);
    }
}