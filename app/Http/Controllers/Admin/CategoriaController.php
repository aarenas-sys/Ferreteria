<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CategoriaController extends Controller
{
    /**
     * Mostrar lista de categorías
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $categorias = Categoria::query()
            ->when($search, function ($query) use ($search) {
                $query->search($search);
            })
            ->ordenadas()
            ->paginate(15)
            ->withQueryString();

        return view('admin.categorias.index', compact('categorias', 'search'));
    }

    /**
     * Mostrar formulario para crear categoría
     */
    public function create(): View
    {
        return view('admin.categorias.create');
    }

    /**
     * Guardar nueva categoría
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con este nombre.',
        ]);

        Categoria::create($validated);

        return redirect()->route('admin.categorias.index')
                       ->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Mostrar formulario para editar categoría
     */
    public function edit(Categoria $categoria): View
    {
        return view('admin.categorias.edit', compact('categoria'));
    }

    /**
     * Actualizar categoría
     */
    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con este nombre.',
        ]);

        $categoria->update($validated);

        return redirect()->route('admin.categorias.index')
                       ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Eliminar categoría
     */
    public function destroy(Categoria $categoria): RedirectResponse
    {
        // Verificar si hay productos en esta categoría
        if ($categoria->productos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una categoría que contiene productos. Primero reasigne los productos a otra categoría.');
        }

        $categoria->delete();

        return redirect()->route('admin.categorias.index')
                       ->with('success', 'Categoría eliminada exitosamente.');
    }
}
