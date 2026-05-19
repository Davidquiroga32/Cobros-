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

        if ($search = $request->get('buscar')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('cedula', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        if ($request->get('filtro') === 'mora') {
            $query->enMora();
        }

        $clientes = $query->orderBy('nombre')->paginate(12)->withQueryString();

        $totalClientes  = $cobrador->clientes()->activos()->count();
        $clientesEnMora = $cobrador->clientes()->enMora()->count();

        $saldoTotal = Cliente::query()
            ->where('clientes.cobrador_id', $cobrador->id)
            ->join('creditos', 'clientes.id', '=', 'creditos.cliente_id')
            ->whereIn('creditos.estado', ['activo', 'mora', 'al_dia'])
            ->selectRaw('COALESCE(SUM(creditos.saldo_pendiente), 0) as total')
            ->value('total') ?? 0;

        return view('cobrador.clientes.index', compact(
            'clientes', 'totalClientes', 'clientesEnMora', 'saldoTotal'
        ));
    }

    public function show(Cliente $cliente)
    {
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

    public function create()
    {
        return view('cobrador.clientes.create');
    }

    public function store(Request $request)
    {
        $cobrador = Auth::user();

        $validated = $request->validate([
            'nombre'               => ['required', 'string', 'max:255'],
            'cedula'               => ['nullable', 'string', 'max:20', 'unique:clientes'],
            'telefono'             => ['required', 'string', 'max:20'],
            'telefono_alt'         => ['nullable', 'string', 'max:20'],
            'direccion'            => ['required', 'string', 'max:500'],
            'barrio'               => ['nullable', 'string', 'max:100'],
            'ciudad'               => ['nullable', 'string', 'max:100'],
            'referencia_ubicacion' => ['nullable', 'string', 'max:500'],
            'notas'                => ['nullable', 'string', 'max:1000'],
        ], [
            'nombre.required'    => 'El nombre es obligatorio.',
            'telefono.required'  => 'El teléfono es obligatorio.',
            'direccion.required' => 'La dirección es obligatoria.',
            'cedula.unique'      => 'Ya existe un cliente con esa cédula.',
        ]);

        $cliente = Cliente::create(array_merge($validated, [
            'cobrador_id' => $cobrador->id,
            'estado'      => 'activo',
            'ciudad'      => $validated['ciudad'] ?? 'Villavicencio',
        ]));

        return redirect()->route('cobrador.clientes.show', $cliente)
            ->with('success', "✅ Cliente '{$cliente->nombre}' registrado exitosamente.");
    }

    public function edit(Cliente $cliente)
    {
        abort_if($cliente->cobrador_id !== Auth::id(), 403);

        return view('cobrador.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        abort_if($cliente->cobrador_id !== Auth::id(), 403);

        $validated = $request->validate([
            'nombre'               => ['required', 'string', 'max:255'],
            'cedula'               => ['nullable', 'string', 'max:20', "unique:clientes,cedula,{$cliente->id}"],
            'telefono'             => ['required', 'string', 'max:20'],
            'telefono_alt'         => ['nullable', 'string', 'max:20'],
            'direccion'            => ['required', 'string', 'max:500'],
            'barrio'               => ['nullable', 'string', 'max:100'],
            'ciudad'               => ['nullable', 'string', 'max:100'],
            'referencia_ubicacion' => ['nullable', 'string', 'max:500'],
            'notas'                => ['nullable', 'string', 'max:1000'],
        ], [
            'nombre.required'    => 'El nombre es obligatorio.',
            'telefono.required'  => 'El teléfono es obligatorio.',
            'direccion.required' => 'La dirección es obligatoria.',
            'cedula.unique'      => 'Ya existe un cliente con esa cédula.',
        ]);

        $cliente->update($validated);

        return redirect()->route('cobrador.clientes.show', $cliente)
            ->with('success', "✅ Cliente actualizado.");
    }
}