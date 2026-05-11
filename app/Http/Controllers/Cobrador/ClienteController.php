<?php

namespace App\Http\Controllers\Cobrador;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $cobrador = Auth::user();

        $query = Cliente::with(['creditos' => fn ($q) => $q->activos()])
            ->delCobrador($cobrador->id)
            ->activos();

        // Búsqueda
        if ($search = $request->get('buscar')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('cedula', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        // Filtro de mora
        if ($request->get('filtro') === 'mora') {
            $query->enMora();
        }

        $clientes = $query->orderBy('nombre')->paginate(12)->withQueryString();

        // Estadísticas
        $totalClientes  = $cobrador->clientes()->activos()->count();
        $clientesEnMora = $cobrador->clientes()->enMora()->count();
        $saldoTotal     = Cliente::delCobrador($cobrador->id)
            ->join('creditos', 'clientes.id', '=', 'creditos.cliente_id')
            ->whereIn('creditos.estado', ['activo', 'mora', 'al_dia'])
            ->sum('creditos.saldo_pendiente');

        return view('cobrador.clientes.index', compact(
            'clientes', 'totalClientes', 'clientesEnMora', 'saldoTotal'
        ));
    }

    public function show(Cliente $cliente)
    {
        // Solo puede ver sus propios clientes
        abort_if($cliente->cobrador_id !== Auth::id(), 403);

        $cliente->load([
            'creditos.cuotas',
            'pagos' => fn ($q) => $q->latest()->take(20),
        ]);

        $creditoActivo   = $cliente->creditoActivo();
        $historialPagos  = $cliente->pagos()->with('cobrador')->latest()->take(10)->get();
        $cuotasProximas  = $creditoActivo
            ? $creditoActivo->cuotas()
                ->whereIn('estado', ['pendiente', 'parcial', 'vencida'])
                ->orderBy('fecha_vencimiento')
                ->take(5)
                ->get()
            : collect();

        return view('cobrador.clientes.show', compact(
            'cliente', 'creditoActivo', 'historialPagos', 'cuotasProximas'
        ));
    }
}