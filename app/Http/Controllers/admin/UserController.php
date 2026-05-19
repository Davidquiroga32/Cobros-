<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('sector')
            ->withCount(['clientes', 'pagos'])
            ->withSum('pagos', 'monto_pagado');

        if ($search = $request->get('buscar')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('cn', 'like', "%{$search}%");
            });
        }

        if ($rol = $request->get('rol')) {
            $query->where('role', $rol);
        }

        if ($sector = $request->get('sector_id')) {
            $query->where('sector_id', $sector);
        }

        $usuarios = $query->orderBy('role')->orderBy('name')->paginate(15)->withQueryString();
        $sectores = Sector::activos()->orderBy('nombre')->get();

        $totalCobradores = User::where('role', 'cobrador')->count();
        $totalAdmins     = User::where('role', 'admin')->count();
        $cobradoHoy      = \App\Models\Pago::whereDate('fecha_pago', today())->sum('monto_pagado');

        return view('admin.usuarios.index', compact(
            'usuarios', 'sectores', 'totalCobradores', 'totalAdmins', 'cobradoHoy'
        ));
    }

    public function create()
    {
        $sectores = Sector::activos()->orderBy('nombre')->get();
        return view('admin.usuarios.create', compact('sectores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'  => ['required', 'confirmed', Rules\Password::defaults()],
            'role'      => ['required', 'in:admin,cobrador'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'cn'        => ['nullable', 'string', 'max:20', 'unique:users'],
            'sector_id' => ['nullable', 'exists:sectores,id'],
        ]);

        // Solo cobradores llevan CN
        $cn = $request->role === 'cobrador' ? $request->cn : null;

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'phone'     => $request->phone,
            'cn'        => $cn,
            'sector_id' => $request->role === 'cobrador' ? $request->sector_id : null,
            'active'    => true,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "✅ Usuario '{$request->name}' creado exitosamente.");
    }

    public function edit(User $usuario)
    {
        $sectores = Sector::activos()->orderBy('nombre')->get();
        $usuario->load(['clientes', 'sector']);
        return view('admin.usuarios.edit', compact('usuario', 'sectores'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', "unique:users,email,{$usuario->id}"],
            'role'      => ['required', 'in:admin,cobrador'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'cn'        => ['nullable', 'string', 'max:20', "unique:users,cn,{$usuario->id}"],
            'sector_id' => ['nullable', 'exists:sectores,id'],
        ]);

        $usuario->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'role'      => $request->role,
            'phone'     => $request->phone,
            'cn'        => $request->role === 'cobrador' ? $request->cn : null,
            'sector_id' => $request->role === 'cobrador' ? $request->sector_id : null,
            'active'    => $request->boolean('active'),
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