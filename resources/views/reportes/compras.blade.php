<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Reporte de Compras') }}
            </h2>
            <a href="{{ route('reportes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    <form method="GET" action="{{ route('reportes.compras') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            @if(auth()->user()->role === 'admin')
                            <div>
                                <label for="sucursal_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sucursal</label>
                                <select name="sucursal_id" id="sucursal_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleccionar sucursal</option>
                                    @foreach($sucursales ?? [] as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ (request('sucursal_id') == $sucursal->id) ? 'selected' : '' }}>
                                            {{ $sucursal->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supervisor</label>
                                <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todos los supervisores</option>
                                    @foreach($usuarios ?? [] as $usuario)
                                        <option value="{{ $usuario->id }}" {{ (request('user_id') == $usuario->id) ? 'selected' : '' }}>
                                            {{ $usuario->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div>
                                <label for="proveedor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Proveedor</label>
                                <select name="proveedor_id" id="proveedor_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todos los proveedores</option>
                                    @foreach($proveedores ?? [] as $proveedor)
                                        <option value="{{ $proveedor->id }}" {{ (request('proveedor_id') == $proveedor->id) ? 'selected' : '' }}>
                                            {{ $proveedor->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Fin</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($compras))
            <!-- Resumen -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 transition-colors duration-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Resumen de Compras</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-700">
                            <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Compras</div>
                            <div class="text-2xl font-bold text-blue-900 dark:text-blue-300">{{ $compras->count() }}</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-700">
                            <div class="text-sm text-green-600 dark:text-green-400 font-medium">Compras Recibidas</div>
                            <div class="text-2xl font-bold text-green-900 dark:text-green-300">{{ $compras->where('estado', 'recibida')->count() }}</div>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-700">
                            <div class="text-sm text-purple-600 dark:text-purple-400 font-medium">Costo Total de Compras</div>
                            <div class="text-2xl font-bold text-purple-900 dark:text-purple-300">${{ number_format($compras->sum(function($compra) { return $compra->total_real ?? $compra->total_estimado; }), 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Compras -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-200">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Historial de Compras</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('reportes.export.pdf', array_merge(request()->query(), ['tipo' => 'compras'])) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                Exportar PDF
                            </a>
                            <a href="{{ route('reportes.export.excel', array_merge(request()->query(), ['tipo' => 'compras'])) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Exportar Excel
                            </a>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse($compras as $index => $compra)
                        <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm transition-shadow duration-200 hover:shadow-md">
                            <!-- Encabezado de la compra -->
                            <button type="button" 
                                    onclick="toggleProducts({{ $compra->id }})" 
                                    class="w-full px-6 py-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 flex justify-between items-center border-b border-gray-200 dark:border-gray-600 transition-colors duration-200">
                                <div class="flex-1 text-left">
                                    <div class="grid grid-cols-7 gap-4 text-sm">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $compra->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Proveedor</p>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $compra->proveedor ? $compra->proveedor->nombre : 'Sin proveedor' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Supervisor</p>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $compra->usuario ? $compra->usuario->name : 'Sin usuario' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Sucursal</p>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $compra->sucursal ? $compra->sucursal->name : 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Estimado</p>
                                            <p class="font-medium text-blue-600">${{ number_format($compra->total_estimado, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Real</p>
                                            <p class="font-medium text-green-600">${{ number_format($compra->total_real ?? 0, 2) }}</p>
                                        </div>
                                        <div>
                                            <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full
                                                @if($compra->estado === 'recibida')
                                                    bg-green-100 text-green-800
                                                @elseif($compra->estado === 'parcial')
                                                    bg-yellow-100 text-yellow-800
                                                @elseif($compra->estado === 'pendiente_confirmacion')
                                                    bg-blue-100 text-blue-800
                                                @elseif($compra->estado === 'pendiente')
                                                    bg-red-100 text-red-800
                                                @else
                                                    bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $compra->estado)) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <svg id="icon-{{ $compra->id }}" 
                                         class="w-5 h-5 text-gray-400 dark:text-gray-500 transform transition-transform"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                            </button>

                            <!-- Detalles de productos (colapsible) -->
                            <div id="products-{{ $compra->id }}" 
                                 class="hidden px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-600 transition-colors duration-200">
                                @if($compra->detalles->count() > 0)
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Productos ({{ $compra->detalles->count() }})</h4>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead class="bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                                <tr>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Producto</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Solicitada</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Recibida</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Pendiente</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Precio</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                                @foreach($compra->detalles as $detalle)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                                        {{ $detalle->producto ? $detalle->producto->nombre : 'Producto eliminado' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-900 dark:text-gray-100">
                                                        {{ $detalle->cantidad_solicitada }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right">
                                                        <span class="px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded text-xs font-medium">
                                                            {{ $detalle->cantidad_recibida }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-right">
                                                        @php
                                                            $pendiente = $detalle->cantidad_solicitada - $detalle->cantidad_recibida;
                                                        @endphp
                                                        <span class="px-2 py-1 {{ $pendiente > 0 ? 'bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-300' : 'bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-300' }} rounded text-xs font-medium">
                                                            {{ $pendiente }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-900 dark:text-gray-100">
                                                        ${{ number_format($detalle->precio_compra, 2) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-medium text-gray-900 dark:text-gray-100">
                                                        ${{ number_format($detalle->subtotal, 2) }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-gray-100 dark:bg-gray-700 border-t-2 border-gray-300 dark:border-gray-600">
                                                <tr>
                                                    <td colspan="5" class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">Total:</td>
                                                    <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                                                        ${{ number_format($compra->detalles->sum('subtotal'), 2) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-gray-500 dark:text-gray-400 italic">No hay productos registrados en esta compra.</p>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No se encontraron compras con los filtros aplicados.</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Script para toggle de productos -->
                    <script>
                        function toggleProducts(compraId) {
                            const productsDiv = document.getElementById('products-' + compraId);
                            const icon = document.getElementById('icon-' + compraId);
                            
                            if (productsDiv.classList.contains('hidden')) {
                                productsDiv.classList.remove('hidden');
                                icon.style.transform = 'rotate(180deg)';
                            } else {
                                productsDiv.classList.add('hidden');
                                icon.style.transform = 'rotate(0deg)';
                            }
                        }

                        // Cargar supervisores dinámicamente según la sucursal
                        document.addEventListener('DOMContentLoaded', function() {
                            const sucursalSelect = document.getElementById('sucursal_id');
                            const usuarioSelect = document.getElementById('usuario_id');

                            if (sucursalSelect) {
                                sucursalSelect.addEventListener('change', function() {
                                    const sucursalId = this.value;
                                    
                                    if (sucursalId) {
                                        // Hacer llamada AJAX para obtener supervisores de la sucursal
                                        const baseUrl = '{{ route("reportes.usuarios-por-sucursal", ["sucursal_id" => "PLACEHOLDER"]) }}';
                                        const url = baseUrl.replace('PLACEHOLDER', sucursalId);
                                        
                                        fetch(url)
                                            .then(response => response.json())
                                            .then(data => {
                                                // Limpiar opciones excepto la primera
                                                usuarioSelect.innerHTML = '<option value="">Todos los supervisores</option>';
                                                
                                                // Agregar nuevas opciones
                                                data.forEach(usuario => {
                                                    const option = document.createElement('option');
                                                    option.value = usuario.id;
                                                    option.textContent = usuario.name;
                                                    usuarioSelect.appendChild(option);
                                                });
                                            })
                                            .catch(error => {
                                                console.error('Error al cargar supervisores:', error);
                                                usuarioSelect.innerHTML = '<option value="">Todos los supervisores</option>';
                                            });
                                    } else {
                                        // Si no hay sucursal seleccionada, limpiar supervisores
                                        usuarioSelect.innerHTML = '<option value="">Todos los supervisores</option>';
                                    }
                                });
                            }
                        });
                    </script>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>