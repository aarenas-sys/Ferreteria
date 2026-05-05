<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Reporte de Ventas') }}
                @if(request('sucursal_id'))
                    - {{ $sucursales->where('id', request('sucursal_id'))->first()->name ?? 'Sucursal' }}
                @else
                    - Todas las Sucursales
                @endif
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
                    <form method="GET" action="{{ route('reportes.ventas') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            @if(auth()->user()->role === 'admin')
                            <div>
                                <label for="sucursal_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sucursal</label>
                                <select name="sucursal_id" id="sucursal_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todas las sucursales</option>
                                    @foreach($sucursales ?? [] as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ (request('sucursal_id') == $sucursal->id) ? 'selected' : '' }}>
                                            {{ $sucursal->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sucursal</label>
                                <div class="mt-1 block w-full px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-100 font-medium">
                                    {{ $sucursales->first()?->name ?? 'Mi Sucursal' }}
                                </div>
                            </div>
                            @endif

                            <div>
                                <label for="tipo_venta" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Venta</label>
                                <select name="tipo_venta" id="tipo_venta" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todos</option>
                                    <option value="contado" {{ (request('tipo_venta') == 'contado') ? 'selected' : '' }}>Contado</option>
                                    <option value="credito" {{ (request('tipo_venta') == 'credito') ? 'selected' : '' }}>Crédito</option>
                                </select>
                            </div>

                            <div>
                                <label for="usuario_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cajero</label>
                                <select name="usuario_id" id="usuario_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todos los cajeros</option>
                                    @foreach($usuarios ?? [] as $usuario)
                                        <option value="{{ $usuario->id }}" {{ (request('usuario_id') == $usuario->id) ? 'selected' : '' }}>
                                            {{ $usuario->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Desde</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hasta</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="flex items-end justify-between">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Filtrar
                                </button>
                                <a href="{{ route('reportes.ventas') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600">
                                    Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($ventas))
            <!-- Totales -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="mb-8 pb-6 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">
                            RESUMEN DE VENTAS
                            @if(request('sucursal_id'))
                                ({{ $sucursales->where('id', request('sucursal_id'))->first()->name ?? 'Sucursal' }})
                            @else
                                (Todas las Sucursales)
                            @endif
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Dinero generado por operaciones, sin importar si se cobró</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border-2 border-blue-300 dark:border-blue-700">
                                <div class="text-sm text-blue-600 dark:text-blue-400 font-semibold">Ventas Contado</div>
                                <div class="text-3xl font-bold text-blue-900 dark:text-blue-300">${{ number_format($totales['ventas_contado'], 2) }}</div>
                                <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">Dinero cobrado inmediatamente</div>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border-2 border-purple-300 dark:border-purple-700">
                                <div class="text-sm text-purple-600 dark:text-purple-400 font-semibold">Ventas Crédito</div>
                                <div class="text-3xl font-bold text-purple-900 dark:text-purple-300">${{ number_format($totales['ventas_credito'], 2) }}</div>
                                <div class="text-xs text-purple-600 dark:text-purple-400 mt-1">Dinero generado, pero pendiente de cobro</div>
                            </div>
                            <div class="bg-gradient-to-br from-blue-100 dark:from-blue-900/20 to-purple-100 dark:to-purple-900/20 p-4 rounded-lg border-2 border-gray-300 dark:border-gray-600">
                                <div class="text-sm text-gray-700 dark:text-gray-300 font-bold"> Total Ventas</div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($totales['total_ventas'], 2) }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Suma de contado + crédito</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">
                            FLUJO REAL DE DINERO EN CAJA
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Dinero que realmente ingresó/egresó de la caja</p>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border-2 border-green-400 dark:border-green-700">
                                <div class="text-sm text-green-700 dark:text-green-400 font-semibold"> Ingresos Contado</div>
                                <div class="text-2xl font-bold text-green-900 dark:text-green-300">${{ number_format($totales['ingresos_contado'], 2) }}</div>
                                <div class="text-xs text-green-700 dark:text-green-400 mt-1">Efectivo recibido</div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border-2 border-green-400 dark:border-green-700">
                                <div class="text-sm text-green-700 dark:text-green-400 font-semibold"> Pagos de Crédito</div>
                                <div class="text-2xl font-bold text-green-900 dark:text-green-300">${{ number_format($totales['pagos_credito'], 2) }}</div>
                                <div class="text-xs text-green-700 dark:text-green-400 mt-1">Cobros de ventas anteriores</div>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border-2 border-red-400 dark:border-red-700">
                                <div class="text-sm text-red-700 dark:text-red-400 font-semibold"> Devoluciones</div>
                                <div class="text-2xl font-bold text-red-900 dark:text-red-300">-${{ number_format($totales['devoluciones'], 2) }}</div>
                                <div class="text-xs text-red-700 dark:text-red-400 mt-1">Dinero reembolsado</div>
                            </div>
                            <div class="bg-gradient-to-br from-green-100 dark:from-green-900/20 to-green-200 dark:to-green-900/30 p-4 rounded-lg border-2 border-green-600 dark:border-green-700">
                                <div class="text-sm text-green-800 dark:text-green-300 font-bold"> Total Caja Real</div>
                                <div class="text-2xl font-bold text-green-900 dark:text-green-300">${{ number_format($totales['total_caja'], 2) }}</div>
                                <div class="text-xs text-green-800 dark:text-green-300 mt-1">Dinero neto en caja</div>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-300 dark:border-gray-600 transition-colors duration-200">
                            <p class="text-xs text-gray-700 dark:text-gray-300">
                                <span class="font-semibold"> Nota:</span> El Total Caja Real 
                                <span class="font-mono bg-gray-50 dark:bg-gray-800 px-2 py-1 rounded transition-colors duration-200">= Ingresos Contado + Pagos Crédito - Devoluciones</span>
                                <br>No incluye ventas a crédito pendientes de cobro (aparecen en Total Ventas solo)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Ventas -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-200">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Detalle de Ventas</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('reportes.export.pdf', array_merge(request()->query(), ['tipo' => 'ventas'])) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                Exportar PDF
                            </a>
                            <a href="{{ route('reportes.export.excel', array_merge(request()->query(), ['tipo' => 'ventas'])) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Exportar Excel
                            </a>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse($ventas as $venta)
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <!-- Encabezado de la venta -->
                            <button type="button" 
                                    onclick="toggleProducts({{ $venta->id }})" 
                                    class="w-full px-6 py-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 flex justify-between items-center border-b border-gray-200 dark:border-gray-600 transition-colors duration-200">
                                <div class="flex-1 text-left">
                                    <div class="grid grid-cols-6 gap-4 text-sm">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Cliente</p>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $venta->cliente ? $venta->cliente->nombre_completo : 'Cliente General' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Cajero</p>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $venta->usuario ? $venta->usuario->name : 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Tipo</p>
                                            <span class="inline-block px-2 py-1 text-xs leading-5 font-semibold rounded-full
                                                {{ $venta->tipo_venta === 'contado' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ ucfirst($venta->tipo_venta) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                                            <p class="font-bold text-gray-900 dark:text-gray-100">${{ number_format($venta->total, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Estado</p>
                                            <span class="inline-block px-2 py-1 text-xs leading-5 font-semibold rounded-full
                                                {{ $venta->estado === 'completada' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($venta->estado) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <svg id="icon-{{ $venta->id }}" 
                                         class="w-5 h-5 text-gray-400 dark:text-gray-500 transform transition-transform"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                            </button>

                            <!-- Detalles de productos (colapsible) -->
                            <div id="products-{{ $venta->id }}" 
                                 class="hidden px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-600 transition-colors duration-200">
                                @if($venta->detalles && $venta->detalles->count() > 0)
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3"> Productos ({{ $venta->detalles->count() }})</h4>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead class="bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                                <tr>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Código</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Producto</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Cantidad</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Precio Unit.</th>
                                                    <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                                @foreach($venta->detalles as $detalle)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                                        {{ $detalle->producto ? $detalle->producto->codigo : 'N/A' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                                        {{ $detalle->producto ? $detalle->producto->nombre : 'Producto eliminado' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-900 dark:text-gray-100">
                                                        {{ $detalle->cantidad }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-900 dark:text-gray-100">
                                                        ${{ number_format($detalle->precio_unitario, 2) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                                                        ${{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-center text-gray-500 dark:text-gray-400 italic">No hay productos registrados</p>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No se encontraron ventas con los filtros aplicados.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        // Función para expandir/contraer detalles de productos
        function toggleProducts(ventaId) {
            const row = document.getElementById(`products-${ventaId}`);
            const icon = document.getElementById(`icon-${ventaId}`);
            
            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                row.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const sucursalSelect = document.getElementById('sucursal_id');
            const usuarioSelect = document.getElementById('usuario_id');
            
            // Solo aplicar filtro dinámico si es admin (sucursal_id existe)
            @if(auth()->user()->role === 'admin')
            // Guardar las opciones originales de usuarios
            const allUsers = Array.from(usuarioSelect.options);

            function filterUsers() {
                const selectedSucursalId = sucursalSelect.value;

                // Limpiar opciones actuales
                usuarioSelect.innerHTML = '<option value="">Todos los cajeros</option>';

                if (selectedSucursalId === '') {
                    // Mostrar todos los usuarios si no hay sucursal seleccionada
                    allUsers.forEach(option => {
                        if (option.value !== '') {
                            usuarioSelect.appendChild(option.cloneNode(true));
                        }
                    });
                } else {
                    // Filtrar usuarios por sucursal
                    fetch(`{{ url('/reportes/usuarios-por-sucursal') }}/${selectedSucursalId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(user => {
                                const option = document.createElement('option');
                                option.value = user.id;
                                option.textContent = user.name;
                                if (user.id == '{{ request("usuario_id") }}') {
                                    option.selected = true;
                                }
                                usuarioSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error al cargar usuarios:', error);
                        });
                }
            }

            // Filtrar usuarios cuando cambia la sucursal
            if (sucursalSelect) {
                sucursalSelect.addEventListener('change', filterUsers);
            }

            // Filtrar inicialmente si hay una sucursal seleccionada
            if (sucursalSelect && sucursalSelect.value !== '') {
                filterUsers();
            }
            @endif
        });
    </script>
</x-app-layout>