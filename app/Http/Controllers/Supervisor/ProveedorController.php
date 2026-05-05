<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisor\StoreProveedorRequest;
use App\Http\Requests\Supervisor\UpdateProveedorRequest;
use App\Models\Proveedor;
use App\Models\Compra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $proveedores = Proveedor::search($search)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('supervisor.proveedores.index', compact('proveedores', 'search'));
    }

    public function create(): View
    {
        return view('supervisor.proveedores.create');
    }

    public function store(StoreProveedorRequest $request): RedirectResponse
    {
        try {
            $data = array_merge($request->validated(), [
                'activo' => $request->has('activo'),
            ]);

            Proveedor::create($data);

            return redirect()->route('supervisor.proveedores.index')
                ->with('success', 'Proveedor creado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el proveedor. Por favor, inténtelo de nuevo.');
        }
    }

    public function show(Proveedor $proveedor): View
    {
        return view('supervisor.proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor): View
    {
        return view('supervisor.proveedores.edit', compact('proveedor'));
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor): RedirectResponse
    {
        try {
            $data = array_merge($request->validated(), [
                'activo' => $request->has('activo'),
            ]);

            $proveedor->update($data);

            return redirect()->route('supervisor.proveedores.index')
                ->with('success', 'Proveedor actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el proveedor. Por favor, inténtelo de nuevo.');
        }
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        try {
            // Verificar si el proveedor tiene registros de compra asociados
            $tieneCompras = Compra::where('proveedor_id', $proveedor->id)->exists();

            if ($tieneCompras) {
                return redirect()->route('supervisor.proveedores.index')
                    ->with('error', 'No se puede eliminar el proveedor porque tiene registros de compra asociados.');
            }

            // Si no hay compras, permitir eliminación
            $proveedor->delete();

            return redirect()->route('supervisor.proveedores.index')
                ->with('success', 'Proveedor eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('supervisor.proveedores.index')
                ->with('error', 'Error al eliminar el proveedor. Por favor, inténtelo de nuevo.');
        }
    }
}
