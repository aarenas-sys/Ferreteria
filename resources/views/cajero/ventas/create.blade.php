<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Crear Nueva Venta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6">Crear Nueva Venta</h1>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <h3 class="font-bold">Errores de validación:</h3>
                    <ul class="mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <strong>Error:</strong> {{ session('error') }}
                </div>
            @endif

            <form id="ventaForm" action="{{ route('cajero.ventas.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Tipo de Venta -->
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tipo de Venta</label>
                    <div class="space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo_venta" value="contado" checked class="form-radio text-blue-600 dark:text-blue-400" onchange="toggleClienteFields()">
                            <span class="ml-2 text-gray-900 dark:text-gray-100">Contado</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo_venta" value="credito" class="form-radio text-blue-600 dark:text-blue-400" onchange="toggleClienteFields()">
                            <span class="ml-2 text-gray-900 dark:text-gray-100">Crédito</span>
                        </label>
                    </div>
                    @error('tipo_venta')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cliente -->
                <div id="clienteField" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <label for="cliente_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cliente (opcional)</label>
                    <select name="cliente_id" id="cliente_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Cliente no registrado</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre_completo }} - {{ $cliente->documento }} ({{ $cliente->estado_credito }})</option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Para ventas a crédito, debe seleccionar un cliente registrado con estado activo.</p>
                    @error('cliente_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha Vencimiento (solo para crédito) -->
                <div id="fechaVencimientoField" class="hidden bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <label for="fecha_vencimiento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('fecha_vencimiento')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descuento -->
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <label for="descuento_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descuento</label>
                    <select name="descuento_id" id="descuento_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="updateTotals()">
                        <option value="">Sin descuento</option>
                        @foreach($descuentos as $descuento)
                            <option value="{{ $descuento->id }}" data-type="{{ $descuento->type }}" data-value="{{ $descuento->value }}" {{ old('descuento_id') == $descuento->id ? 'selected' : '' }}>
                                {{ $descuento->name }} - {{ $descuento->type === 'percentage' ? $descuento->value . '%' : '$' . number_format($descuento->value, 2) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Selecciona el descuento configurado por el administrador para esta venta.</p>
                    @error('descuento_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Productos -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Productos</label>
                    <div id="productosContainer">
                        <div class="producto-row flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                            <select name="productos[0][id]" class="producto-select flex-1 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Seleccionar producto</option>
                                @foreach($productos as $producto)
                                    <option value="{{ $producto->id }}" data-precio="{{ $producto->precio }}" data-stock="{{ $producto->stock }}">
                                        {{ $producto->nombre }} - ${{ number_format($producto->precio, 2) }} (Stock: {{ $producto->stock }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" name="productos[0][cantidad]" placeholder="Cantidad" min="1" class="cantidad-input w-full sm:w-24 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" class="remove-product bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md transition duration-200" style="display: none;">Eliminar</button>
                        </div>
                    </div>
                    <button type="button" id="addProduct" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200">Agregar Producto</button>
                    @error('productos')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    @error('productos.*')
                        <p class="text-red-500 text-sm mt-1">Error en productos</p>
                    @enderror
                </div>

                <!-- Totales -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Resumen de la Venta</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Subtotal</label>
                            <span id="subtotal" class="text-xl font-bold text-gray-800 dark:text-gray-200">$0.00</span>
                        </div>
                        <div class="text-center">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">IVA</label>
                            <span id="iva" class="text-xl font-bold text-green-600 dark:text-green-400">$0.00</span>
                        </div>
                        <div class="text-center">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Descuento</label>
                            <span id="descuento" class="text-xl font-bold text-red-600 dark:text-red-400">$0.00</span>
                        </div>
                        <div class="text-center">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Total</label>
                            <span id="total" class="text-2xl font-bold text-blue-600 dark:text-blue-400">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200 dark:border-gray-600">
                    <a href="{{ route('cajero') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-md transition duration-200 text-center">Cancelar</a>
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-md transition duration-200 font-semibold">Crear Venta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let productIndex = 1;

document.getElementById('addProduct').addEventListener('click', function() {
    const container = document.getElementById('productosContainer');
    const newRow = document.createElement('div');
    newRow.className = 'producto-row flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-md';
    newRow.innerHTML = `
        <select name="productos[${productIndex}][id]" class="producto-select flex-1 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Seleccionar producto</option>
            @foreach($productos as $producto)
                <option value="{{ $producto->id }}" data-precio="{{ $producto->precio }}" data-stock="{{ $producto->stock }}">
                    {{ $producto->nombre }} - ${{ number_format($producto->precio, 2) }} (Stock: {{ $producto->stock }})
                </option>
            @endforeach
        </select>
        <input type="number" name="productos[${productIndex}][cantidad]" placeholder="Cantidad" min="1" class="cantidad-input w-full sm:w-24 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="button" class="remove-product bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md transition duration-200">Eliminar</button>
    `;
    container.appendChild(newRow);
    productIndex++;
    updateTotals();
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-product')) {
        e.target.closest('.producto-row').remove();
        updateTotals();
    }
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('producto-select') || e.target.classList.contains('cantidad-input')) {
        updateTotals();
    }
});

function updateTotals() {
    let subtotal = 0;
    const rows = document.querySelectorAll('.producto-row');

    rows.forEach(row => {
        const select = row.querySelector('.producto-select');
        const cantidad = row.querySelector('.cantidad-input').value || 0;
        const precio = select.options[select.selectedIndex]?.getAttribute('data-precio') || 0;
        subtotal += precio * cantidad;
    });

    const iva = subtotal * 0.19; // 19% IVA

    const discountSelect = document.getElementById('descuento_id');
    let descuento = 0;

    if (discountSelect && discountSelect.value) {
        const selectedOption = discountSelect.options[discountSelect.selectedIndex];
        const discountType = selectedOption.getAttribute('data-type');
        const discountValue = parseFloat(selectedOption.getAttribute('data-value')) || 0;

        if (discountType === 'percentage') {
            descuento = subtotal * (discountValue / 100);
        } else {
            descuento = Math.min(discountValue, subtotal);
        }
    }

    const total = subtotal + iva - descuento;

    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('iva').textContent = '$' + iva.toFixed(2);
    document.getElementById('descuento').textContent = '$' + descuento.toFixed(2);
    document.getElementById('total').textContent = '$' + total.toFixed(2);
}

function toggleClienteFields() {
    const tipoVenta = document.querySelector('input[name="tipo_venta"]:checked').value;
    const clienteField = document.getElementById('clienteField');
    const fechaField = document.getElementById('fechaVencimientoField');
    const clienteSelect = document.getElementById('cliente_id');
    const fechaInput = document.getElementById('fecha_vencimiento');

    if (tipoVenta === 'credito') {
        fechaField.classList.remove('hidden');
        fechaInput.setAttribute('name', 'fecha_vencimiento'); // Agregar name para que se envíe
        fechaInput.required = true;

        // Para crédito, el cliente es obligatorio
        clienteSelect.required = true;
        clienteField.querySelector('label').textContent = 'Cliente (obligatorio para crédito)';
        clienteField.querySelector('p').textContent = 'Debe seleccionar un cliente registrado con estado activo para ventas a crédito.';
    } else {
        fechaField.classList.add('hidden');
        fechaInput.removeAttribute('name'); // Remover name para que no se envíe
        fechaInput.required = false;

        // Para contado, el cliente es opcional
        clienteSelect.required = false;
        clienteField.querySelector('label').textContent = 'Cliente (opcional)';
        clienteField.querySelector('p').textContent = 'Puede seleccionar un cliente registrado o dejar como "Cliente no registrado".';
    }
}

// Validación antes de enviar el formulario
document.getElementById('ventaForm').addEventListener('submit', function(e) {
    const productos = document.querySelectorAll('.producto-select');
    let hasValidProduct = false;

    productos.forEach(select => {
        if (select.value !== '') {
            hasValidProduct = true;
        }
    });

    if (!hasValidProduct) {
        e.preventDefault();
        alert('Debe seleccionar al menos un producto para la venta.');
        return false;
    }

    const tipoVenta = document.querySelector('input[name="tipo_venta"]:checked');
    if (!tipoVenta) {
        e.preventDefault();
        alert('Debe seleccionar un tipo de venta (contado o crédito).');
        return false;
    }

    if (tipoVenta.value === 'credito') {
        const clienteId = document.getElementById('cliente_id').value;
        if (!clienteId) {
            e.preventDefault();
            alert('Para ventas a crédito debe seleccionar un cliente registrado.');
            return false;
        }
    }

    // Mostrar indicador de carga
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Procesando venta...';
});

document.addEventListener('DOMContentLoaded', function() {
    toggleClienteFields();
    updateTotals();
});
</script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>