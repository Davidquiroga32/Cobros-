<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::with([
                'cobrador',
                'creditos' => fn ($q) => $q->whereIn('estado', ['activo', 'al_dia', 'mora'])->latest()->limit(1),
            ])
            ->withCount('creditos');

        if ($search = $request->get('buscar')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('cedula', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        if ($cobrador = $request->get('cobrador_id')) {
            $query->where('cobrador_id', $cobrador);
        }

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        if ($request->get('mora')) {
            $query->whereHas('creditos', fn ($q) => $q->where('estado', 'mora'));
        }

        $clientes   = $query->orderBy('nombre')->paginate(20)->withQueryString();
        $cobradores = User::where('role', 'cobrador')->where('active', true)->orderBy('name')->get();

        $totalClientes   = Cliente::count();
        $clientesActivos = Cliente::where('estado', 'activo')->count();
        $clientesEnMora  = Cliente::whereHas('creditos', fn ($q) => $q->where('estado', 'mora'))->count();

        return view('admin.clientes.index', compact(
            'clientes', 'cobradores', 'totalClientes', 'clientesActivos', 'clientesEnMora'
        ));
    }

    public function create()
    {
        $cobradores = User::where('role', 'cobrador')->where('active', true)->orderBy('name')->get();
        return view('admin.clientes.create', compact('cobradores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'               => ['required', 'string', 'max:255'],
            'cedula'               => ['nullable', 'string', 'max:20', 'unique:clientes'],
            'telefono'             => ['required', 'string', 'max:20'],
            'telefono_alt'         => ['nullable', 'string', 'max:20'],
            'direccion'            => ['required', 'string', 'max:500'],
            'barrio'               => ['nullable', 'string', 'max:100'],
            'ciudad'               => ['nullable', 'string', 'max:100'],
            'referencia_ubicacion' => ['nullable', 'string', 'max:500'],
            'cobrador_id'          => ['required', 'exists:users,id'],
            'notas'                => ['nullable', 'string', 'max:1000'],
        ]);

        $cliente = Cliente::create(array_merge($validated, ['estado' => 'activo']));

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', "✅ Cliente '{$cliente->nombre}' creado exitosamente.");
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'cobrador',
            'creditos.cuotas',
            'pagos' => fn ($q) => $q->latest()->take(20),
        ]);
        $cobradores = User::where('role', 'cobrador')->where('active', true)->orderBy('name')->get();
        return view('admin.clientes.show', compact('cliente', 'cobradores'));
    }

    public function edit(Cliente $cliente)
    {
        $cobradores = User::where('role', 'cobrador')->where('active', true)->orderBy('name')->get();
        return view('admin.clientes.edit', compact('cliente', 'cobradores'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre'               => ['required', 'string', 'max:255'],
            'cedula'               => ['nullable', 'string', 'max:20', "unique:clientes,cedula,{$cliente->id}"],
            'telefono'             => ['required', 'string', 'max:20'],
            'telefono_alt'         => ['nullable', 'string', 'max:20'],
            'direccion'            => ['required', 'string', 'max:500'],
            'barrio'               => ['nullable', 'string', 'max:100'],
            'ciudad'               => ['nullable', 'string', 'max:100'],
            'referencia_ubicacion' => ['nullable', 'string', 'max:500'],
            'cobrador_id'          => ['required', 'exists:users,id'],
            'estado'               => ['required', 'in:activo,inactivo,bloqueado'],
            'notas'                => ['nullable', 'string', 'max:1000'],
        ]);

        $cliente->update($validated);

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', "✅ Cliente actualizado.");
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('admin.clientes.index')
            ->with('success', "Cliente eliminado.");
    }
}