<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount(['clientes', 'pagos'])
            ->withSum('pagos', 'monto_pagado');

        if ($search = $request->get('buscar')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($rol = $request->get('rol')) {
            $query->where('role', $rol);
        }

        $usuarios = $query->orderBy('role')->orderBy('name')->paginate(15)->withQueryString();

        $totalCobradores = User::where('role', 'cobrador')->count();
        $totalAdmins     = User::where('role', 'admin')->count();
        $cobradoHoy      = \App\Models\Pago::whereDate('fecha_pago', today())->sum('monto_pagado');

        return view('admin.usuarios.index', compact(
            'usuarios', 'totalCobradores', 'totalAdmins', 'cobradoHoy'
        ));
    }

    public function create()
    {
        return view('admin.usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'in:admin,cobrador'],
            'phone'    => ['nullable', 'string', 'max:20'],
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'phone'    => $request->phone,
            'active'   => true,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "✅ Usuario '{$request->name}' creado exitosamente.");
    }

    public function edit(User $usuario)
    {
        $usuario->load(['clientes', 'pagos']);
        return view('admin.usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', "unique:users,email,{$usuario->id}"],
            'role'  => ['required', 'in:admin,cobrador'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $usuario->update([
            'name'   => $request->name,
            'email'  => $request->email,
            'role'   => $request->role,
            'phone'  => $request->phone,
            'active' => $request->boolean('active'),
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.usuarios.index')
            ->with('success', "✅ Usuario '{$usuario->name}' actualizado.");
    }

    public function toggleActive(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }
        $usuario->update(['active' => !$usuario->active]);
        $estado = $usuario->active ? 'activado' : 'desactivado';
        return back()->with('success', "Usuario {$estado} correctamente.");
    }
}