<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    public function index(Request $request)
    {
        $query = Sector::withCount(['cobradores as total_cobradores']);

        if ($buscar = $request->get('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }

        $sectores = $query->orderBy('nombre')->paginate(15)->withQueryString();

        return view('admin.sectores.index', compact('sectores'));
    }

    public function create()
    {
        return view('admin.sectores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'ciudad'      => ['required', 'string', 'max:100'],
        ]);

        $sector = Sector::create(array_merge($validated, [
            'codigo' => Sector::generarCodigo(),
            'activo' => true,
        ]));

        return redirect()->route('admin.sectores.index')
            ->with('success', "✅ Sector '{$sector->nombre}' creado correctamente.");
    }

    public function edit(Sector $sector)
    {
        return view('admin.sectores.edit', compact('sector'));
    }

    public function update(Request $request, Sector $sector)
    {
        $validated = $request->validate([
            'nombre'      => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'ciudad'      => ['required', 'string', 'max:100'],
            'activo'      => ['boolean'],
        ]);

        $sector->update($validated);

        return redirect()->route('admin.sectores.index')
            ->with('success', "✅ Sector actualizado correctamente.");
    }

    public function destroy(Sector $sector)
    {
        // No eliminar si tiene cobradores asignados
        if ($sector->cobradores()->exists()) {
            return back()->with('error', '❌ No puedes eliminar un sector con cobradores asignados.');
        }

        $sector->delete();

        return redirect()->route('admin.sectores.index')
            ->with('success', "✅ Sector eliminado.");
    }

    public function toggleActivo(Sector $sector)
    {
        $sector->update(['activo' => !$sector->activo]);
        $estado = $sector->activo ? 'activado' : 'desactivado';
        return back()->with('success', "Sector {$estado} correctamente.");
    }
}