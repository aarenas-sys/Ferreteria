<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Crear compra
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Genera una nueva solicitud de compra para tu sucursal.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($errors->any())
                        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-red-800">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('supervisor.compras.store') }}" method="POST" id="compra-form">
                        @csrf

                        <div class="grid gap-6 sm:grid-cols-2 mb-6">
                            <div>
                                <label for="proveedor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Proveedor</label>
                                <select id="proveedor_id" name="proveedor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
                                    <option value="">Selecciona un proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>{{ $proveedor->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="tipo_pago" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de pago</label>
                                <select id="tipo_pago" name="tipo_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
                                    <option value="">Selecciona un tipo de pago</option>
                                    <option value="contado" {{ old('tipo_pago') == 'contado' ? 'selected' : '' }}>Contado</option>
                                    <option value="credito" {{ old('tipo_pago') == 'credito' ? 'selected' : '' }}>Crédito</option>
                                    <option value="transferencia" {{ old('tipo_pago') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Productos solicitados</h3>
                            <button type="button" id="add-product" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Agregar producto
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="detalles-table">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio compra</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="detalles-body">
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-right">
                            <p class="text-sm text-gray-700 dark:text-gray-300">Total estimado: <span id="total-estimado">0.00</span></p>
                        </div>

                        <div class="mt-6 flex items-center justify-between gap-4">
                            <a href="{{ route('supervisor.compras.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Cancelar</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500">
                                Guardar compra
                            </button>
                        </div>
                    </form>

                    <template id="detalle-row-template">
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <select data-name="detalles[__INDEX__][producto_id]" class="detalle-producto mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
                                    <option value="">Selecciona producto</option>
                                    @foreach($productos as $producto)
                                        <option value="{{ $producto->id }}" data-precio="{{ $producto->precio }}">{{ $producto->nombre }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <input type="number" min="1" value="1" data-name="detalles[__INDEX__][cantidad_solicitada]" class="cantidad-solicitada mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <input type="number" step="0.01" min="0.01" value="0.00" data-name="detalles[__INDEX__][precio_compra]" class="precio-compra mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-200">
                                <span class="subtotal">0.00</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <button type="button" class="remove-detalle text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addProductButton = document.getElementById('add-product');
            const detallesBody = document.getElementById('detalles-body');
            const template = document.getElementById('detalle-row-template');
            const totalEstimado = document.getElementById('total-estimado');
            let index = 0;

            function formatNumber(value) {
                return Number(value).toFixed(2);
            }

            function recalculateRow(row) {
                const cantidadInput = row.querySelector('.cantidad-solicitada');
                const precioInput = row.querySelector('.precio-compra');
                const subtotalSpan = row.querySelector('.subtotal');

                const cantidad = Number(cantidadInput.value) || 0;
                const precio = Number(precioInput.value) || 0;
                subtotalSpan.textContent = formatNumber(cantidad * precio);
                recalculateTotal();
            }

            function recalculateTotal() {
                let total = 0;
                detallesBody.querySelectorAll('tr').forEach((row) => {
                    const subtotal = Number(row.querySelector('.subtotal').textContent) || 0;
                    total += subtotal;
                });
                totalEstimado.textContent = formatNumber(total);
            }

            function addDetalle() {
                const clone = template.content.cloneNode(true);
                clone.querySelectorAll('[data-name]').forEach((element) => {
                    element.name = element.getAttribute('data-name').replace('__INDEX__', index.toString());
                    element.removeAttribute('data-name');
                });

                const row = clone.querySelector('tr');
                row.querySelectorAll('.cantidad-solicitada, .precio-compra').forEach((input) => {
                    input.addEventListener('input', () => recalculateRow(row));
                });
                row.querySelector('.detalle-producto').addEventListener('change', (event) => {
                    const selected = event.target.selectedOptions[0];
                    const priceInput = row.querySelector('.precio-compra');
                    const precio = selected ? selected.dataset.precio : 0;
                    priceInput.value = precio ? Number(precio).toFixed(2) : '0.00';
                    recalculateRow(row);
                });
                row.querySelector('.remove-detalle').addEventListener('click', () => {
                    row.remove();
                    recalculateTotal();
                });

                detallesBody.appendChild(row);
                index++;
            }

            addProductButton.addEventListener('click', addDetalle);
            addDetalle();
        });
    </script>
</x-app-layout>
