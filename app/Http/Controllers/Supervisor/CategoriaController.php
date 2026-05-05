<?php

namespace App\Http\Controllers\Supervisor;

use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoriaController extends Controller
{
    /**
     * Mostrar todas las categorías disponibles
     */
    public function index()
    {
        $categorias = Categoria::ordenadas()->get();
        return response()->json($categorias);
    }

    /**
     * Guardar una nueva categoría
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:categorias,nombre'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]);

        $categoria = Categoria::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente.',
            'categoria' => $categoria,
        ], 201);
    }

    /**
     * Obtener categorías para dropdown (sin paginación)
     */
    public function dropdown()
    {
        $categorias = Categoria::ordenadas()->get(['id', 'nombre']);
        return response()->json($categorias);
    }

    /**
     * Obtener todas las categorías con todos los datos
     */
    public function all()
    {
        $categorias = Categoria::ordenadas()->get();
        return response()->json($categorias);
    }

    /**
     * Actualizar una categoría
     */
    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:categorias,nombre,' . $categoria->id],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe otra categoría con ese nombre.',
        ]);

        $categoria->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente.',
            'categoria' => $categoria,
        ], 200);
    }

    /**
     * Eliminar una categoría
     */
    public function destroy(Categoria $categoria)
    {
        // Verificar si hay productos asociados
        if ($categoria->productos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una categoría que tiene productos asociados.',
            ], 422);
        }

        $nombre = $categoria->nombre;
        $categoria->delete();

        return response()->json([
            'success' => true,
            'message' => "Categoría '{$nombre}' eliminada exitosamente.",
        ], 200);
    }
}
