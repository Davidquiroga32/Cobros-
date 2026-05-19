<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\Pago;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    // ── LISTADO ───────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Caja::with(['cobrador', 'sector', 'abiertaPor'])
            ->latest('fecha_apertura');

        if ($cobradorId = $request->get('cobrador_id')) {
            $query->where('cobrador_id', $cobradorId);
        }

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        // Por defecto muestra hoy; permite filtrar por otra fecha
        $fecha = $request->get('fecha', today()->format('Y-m-d'));
        $query->whereDate('fecha_jornada', $fecha);

        $cajas      = $query->paginate(20)->withQueryString();
        $cobradores = User::where('role', 'cobrador')->where('active', true)->orderBy('name')->get();
        $sectores   = Sector::activos()->orderBy('nombre')->get();

        // KPIs del día seleccionado (sin reutilizar la query paginada)
        $cajasDelDia   = Caja::whereDate('fecha_jornada', $fecha);
        $totalAbierto  = (clone $cajasDelDia)->where('estado', 'abierta')->count();
        $totalCobrado  = (clone $cajasDelDia)->sum('monto_cobrado');
        $totalInicial  = (clone $cajasDelDia)->sum('monto_inicial');

        return view('admin.cajas.index', compact(
            'cajas', 'cobradores', 'sectores',
            'totalAbierto', 'totalCobrado', 'totalInicial'
        ));
    }

    // ── FORMULARIO APERTURA ───────────────────────────────────────────────────

    public function create()
    {
        $cobradores = User::where('role', 'cobrador')
            ->where('active', true)
            ->with('sector')
            ->withCount('clientes')
            ->orderBy('name')
            ->get();

        $sectores = Sector::activos()->orderBy('nombre')->get();

        return view('admin.cajas.create', compact('cobradores', 'sectores'));
    }

    // ── ABRIR CAJA ────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cobrador_id'    => ['required', 'exists:users,id'],
            'sector_id'      => ['nullable', 'exists:sectores,id'],
            'monto_inicial'  => ['required', 'numeric', 'min:0'],
            'notas_apertura' => ['nullable', 'string', 'max:500'],
        ]);

        // Un cobrador no puede tener dos cajas abiertas el mismo día
        $yaExiste = Caja::where('cobrador_id', $validated['cobrador_id'])
            ->whereDate('fecha_jornada', today())
            ->where('estado', 'abierta')
            ->exists();

        if ($yaExiste) {
            return back()
                ->withInput()
                ->with('error', '❌ Este cobrador ya tiene una caja abierta hoy.');
        }

        $cobrador = User::find($validated['cobrador_id']);

        $caja = Caja::create([
            'codigo'         => Caja::generarCodigo(),
            'cobrador_id'    => $validated['cobrador_id'],
            'sector_id'      => $validated['sector_id'] ?? $cobrador->sector_id, // hereda del cobrador
            'abierta_por'    => auth()->id(),
            'monto_inicial'  => $validated['monto_inicial'],
            'monto_cobrado'  => 0,
            'monto_gastos'   => 0,
            'monto_final'    => 0,
            'estado'         => 'abierta',
            'fecha_apertura' => now(),
            'fecha_jornada'  => today(),
            'notas_apertura' => $validated['notas_apertura'] ?? null,
        ]);

        return redirect()->route('admin.cajas.show', $caja)
            ->with('success', "✅ Caja {$caja->codigo} abierta para {$cobrador->name}.");
    }

    // ── DETALLE ───────────────────────────────────────────────────────────────

    public function show(Caja $caja)
    {
        $caja->load(['cobrador.sector', 'sector', 'abiertaPor', 'cerradaPor']);

        // Refrescar total cobrado desde pagos reales antes de mostrar
        // Solo si está abierta para no pisar datos de cierre ya registrados
        if ($caja->estaAbierta()) {
            $caja->sincronizarMontos();
        }

        // Pagos del cobrador en la jornada de esta caja
        $pagos = Pago::where('cobrador_id', $caja->cobrador_id)
            ->whereDate('fecha_pago', $caja->fecha_jornada)
            ->with(['cliente', 'cuota'])
            ->latest('fecha_pago')
            ->get();

        return view('admin.cajas.show', compact('caja', 'pagos'));
    }

    // ── CERRAR CAJA ───────────────────────────────────────────────────────────

    public function cerrar(Request $request, Caja $caja)
    {
        if (! $caja->estaAbierta()) {
            return back()->with('error', '❌ Esta caja ya está cerrada.');
        }

        $request->validate([
            'monto_gastos' => ['nullable', 'numeric', 'min:0'],
            'notas_cierre' => ['nullable', 'string', 'max:500'],
        ]);

        $caja->cerrar(
            cerradaPorId: auth()->id(),
            gastos      : (float) $request->get('monto_gastos', 0),
            notas       : $request->get('notas_cierre'),
        );

        $cajaCerrada = $caja->fresh();

        return redirect()->route('admin.cajas.show', $cajaCerrada)
            ->with('success', "✅ Caja cerrada. Total cobrado: $" . number_format($cajaCerrada->monto_cobrado, 0, ',', '.'));
    }
}