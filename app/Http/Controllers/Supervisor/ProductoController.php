<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisor\StoreProductoRequest;
use App\Http\Requests\Supervisor\UpdateProductoRequest;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Intervention\Image\ImageManagerStatic as Image;
use Cloudinary\Cloudinary;

class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = auth()->user()->branch_id;

        $query = Producto::with('categoria')
                        ->where('sucursal_id', $branchId);

        // Filtro por búsqueda
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        // Filtro por categoría
        if ($request->has('categoria_id') && !empty($request->categoria_id)) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtro por estado de stock
        if ($request->has('estado_stock') && !empty($request->estado_stock)) {
            $query->byEstadoStock($request->estado_stock);
        }

        $productos = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $lowStockCount = Producto::where('sucursal_id', $branchId)
            ->lowStock()
            ->count();

        // Obtener categorías para filtro
        $categorias = Categoria::ordenadas()->get();

        return view('supervisor.productos.index', compact('productos', 'lowStockCount', 'categorias'));
    }

    public function create(): View
    {
        $categorias = Categoria::ordenadas()->get();
        return view('supervisor.productos.create', compact('categorias'));
    }

    public function store(StoreProductoRequest $request): RedirectResponse
    {
        $branchId = auth()->user()->branch_id;

        $data = array_merge($request->validated(), [
            'sucursal_id' => $branchId,
        ]);

        $imagenMensaje = '';
        if ($request->hasFile('imagen')) {
            $rutaImagen = $this->redimensionarYGuardarImagen($request->file('imagen'), 'productos');
            
            if (!$rutaImagen) {
                Log::error('ProductoController::store - No se pudo guardar imagen', [
                    'user_id' => auth()->id(),
                    'branch_id' => $branchId
                ]);
                return redirect()->route('supervisor.productos.create')
                    ->withInput()
                    ->with('error', 'Error al procesar la imagen. Por favor intenta de nuevo.');
            }
            
            $data['imagen'] = $rutaImagen;
            $imagenMensaje = ' La imagen se ha redimensionado a 800x600px automáticamente.';
        }

        Producto::create($data);

        $branchName = auth()->user()->branch?->name ?? 'la sucursal asociada';

        return redirect()->route('supervisor.productos.index')
            ->with('success', "Producto creado correctamente en {$branchName}.{$imagenMensaje}");
    }

    public function show(Producto $producto): View
    {
        $this->authorizeBranch($producto);

        $producto->load('categoria');

        return view('supervisor.productos.show', compact('producto'));
    }

    public function edit(Producto $producto): View
    {
        $this->authorizeBranch($producto);

        $producto->load('categoria');
        $categorias = Categoria::ordenadas()->get();
        return view('supervisor.productos.edit', compact('producto', 'categorias'));
    }

    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        $this->authorizeBranch($producto);

        $data = $request->validated();

        $imagenMensaje = '';
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                try {
                    Storage::disk('public')->delete($producto->imagen);
                    Log::info('ProductoController: Imagen anterior eliminada', ['path' => $producto->imagen]);
                } catch (\Exception $e) {
                    Log::warning('ProductoController: Error eliminando imagen anterior', [
                        'path' => $producto->imagen,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $rutaImagen = $this->redimensionarYGuardarImagen($request->file('imagen'), 'productos');
            
            if (!$rutaImagen) {
                Log::error('ProductoController::update - No se pudo guardar imagen', [
                    'user_id' => auth()->id(),
                    'producto_id' => $producto->id
                ]);
                return redirect()->route('supervisor.productos.edit', $producto)
                    ->withInput()
                    ->with('error', 'Error al procesar la imagen. Por favor intenta de nuevo.');
            }
            
            $data['imagen'] = $rutaImagen;
            $imagenMensaje = ' La imagen se ha redimensionado a 800x600px automáticamente.';
        }

        $producto->update($data);

        return redirect()->route('supervisor.productos.index')
            ->with('success', "Producto actualizado correctamente.{$imagenMensaje}");
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $this->authorizeBranch($producto);

        if ($producto->stock > 0) {
            return redirect()->route('supervisor.productos.index')
                ->with('error', 'No se puede eliminar un producto con stock mayor a 0.');
        }

        // Eliminar imagen si existe en Cloudinary
        if ($producto->imagen) {
            try {
                $cloudinary = new Cloudinary([
                    'cloud' => [
                        'cloud_name' => env('CLOUDINARY_NAME'),
                        'api_key' => env('CLOUDINARY_API_KEY'),
                        'api_secret' => env('CLOUDINARY_API_SECRET'),
                    ]
                ]);
                
                $cloudinary->uploadApi()->destroy($producto->imagen);
                Log::info('Imagen eliminada de Cloudinary', ['public_id' => $producto->imagen]);
            } catch (\Exception $e) {
                Log::warning('Error eliminando imagen de Cloudinary', [
                    'public_id' => $producto->imagen,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $producto->delete();

        return redirect()->route('supervisor.productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    protected function authorizeBranch(Producto $producto): void
    {
        if ($producto->sucursal_id !== auth()->user()->branch_id) {
            abort(403);
        }
    }

    /**
     * Redimensiona una imagen a 800x600px y la guarda en Cloudinary
     *
     * @param \Illuminate\Http\UploadedFile $imagen
     * @param string $directorio
     * @return string|null Public ID de Cloudinary
     */
    private function redimensionarYGuardarImagen($imagen, string $directorio): ?string
    {
        try {
            // Validar que sea una imagen
            if (!$imagen || !$imagen->isValid()) {
                Log::error('ProductoController: Imagen no válida', [
                    'error' => $imagen->getError() ?? 'Archivo inválido'
                ]);
                throw new \Exception('La imagen no es válida');
            }

            // Redimensionar con Intervention Image
            $img = Image::make($imagen)
                ->resize(800, 600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('jpg', 85);

            // Generar nombre único
            $filename = time() . '_' . uniqid();

            // Guardar temporalmente en disco local
            $tempPath = storage_path("app/temp/{$filename}.jpg");
            $tempDir = storage_path("app/temp");
            
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            file_put_contents($tempPath, (string) $img);

            // Subir a Cloudinary
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ]
            ]);

            $result = $cloudinary->uploadApi()->upload($tempPath, [
                'folder' => $directorio,
                'resource_type' => 'auto',
                'public_id' => $filename,
            ]);

            // Eliminar archivo temporal
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            $publicId = $result['public_id'];
            
            Log::info('ProductoController: Imagen subida a Cloudinary', [
                'public_id' => $publicId,
                'url' => $result['secure_url']
            ]);

            return $publicId;

        } catch (\Exception $e) {
            Log::error('ProductoController: Error en redimensionarYGuardarImagen', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile()
            ]);
            
            return null;
        }
    }
}
