<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $cobrador = $request->user();

        $query = Cliente::with(['creditos' => fn ($q) => $q->activos()->latest()->limit(1)])
            ->where('cobrador_id', $cobrador->id)
            ->activos();

        if ($search = $request->get('buscar')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        $clientes = $query->orderBy('nombre')->paginate(20);

        return response()->json($clientes);
    }

    public function show(Cliente $cliente, Request $request)
    {
        if ($cliente->cobrador_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $cliente->load([
            'creditos' => fn ($q) => $q->activos()->with('cuotas'),
            'pagos'    => fn ($q) => $q->latest()->take(10),
        ]);

        return response()->json($cliente);
    }

    public function store(Request $request)
    {
        $cobrador = $request->user();

        $validated = $request->validate([
            'nombre'               => ['required', 'string', 'max:255'],
            'cedula'               => ['nullable', 'string', 'max:20', 'unique:clientes'],
            'telefono'             => ['required', 'string', 'max:20'],
            'direccion'            => ['required', 'string', 'max:500'],
            'barrio'               => ['nullable', 'string', 'max:100'],
            'referencia_ubicacion' => ['nullable', 'string', 'max:500'],
            'latitud'              => ['nullable', 'numeric'],
            'longitud'             => ['nullable', 'numeric'],
        ]);

        $cliente = Cliente::create(array_merge($validated, [
            'cobrador_id' => $cobrador->id,
            'estado'      => 'activo',
            'ciudad'      => 'Villavicencio',
        ]));

        return response()->json($cliente, 201);
    }
}
