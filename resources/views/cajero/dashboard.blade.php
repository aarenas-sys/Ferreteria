<x-app-layout>
    @php
        $cajaAbierta = $cajaAbierta ?? null;
        $cajaCerradaHoy = $cajaCerradaHoy ?? null;
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Panel de Control del Cajero') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("¡Bienvenido al Panel del Cajero!") }}
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Realiza ventas, registra pagos y gestiona devoluciones desde tu caja.</p>

                    @if(!isset($cajaAbierta) || empty($cajaAbierta))
                        @if(isset($cajaCerradaHoy) && $cajaCerradaHoy)
                            <div class="mt-4 rounded-lg border border-green-300 bg-green-50 p-4 text-green-900">
                                <p class="font-semibold">Caja cerrada hoy</p>
                                <p class="mt-2">Ya cerró su caja del día. No puede abrir otra caja hasta mañana.</p>
                                <p class="mt-1 text-sm">Caja cerrada el {{ $cajaCerradaHoy->fecha_cierre->format('d/m/Y H:i') }}</p>
                            </div>
                        @else
                            <div class="mt-4 rounded-lg border border-yellow-300 bg-yellow-50 p-4 text-yellow-900">
                                <p class="font-semibold">Debe abrir caja antes de iniciar operaciones.</p>
                                <p class="mt-2">No podrá crear ventas, registrar devoluciones ni pagos de crédito sin una caja abierta.</p>
                                <a href="{{ route('cajero.caja.abrir.form') }}" class="inline-flex mt-3 items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">Abrir caja</a>
                            </div>
                        @endif
                    @endif

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('cajero.ventas.create') }}" class="block p-6 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Nueva Venta</h3>
                            <p class="mt-2 text-sm">Registra una nueva venta de productos a clientes.</p>
                        </a>

                        <a href="{{ route('cajero.ventas.index') }}" class="block p-6 bg-green-500 hover:bg-green-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Ver Ventas</h3>
                            <p class="mt-2 text-sm">Consulta el histórico de ventas realizadas.</p>
                        </a>

                        <a href="{{ route('cajero.creditos.index') }}" class="block p-6 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Pagos de Crédito</h3>
                            <p class="mt-2 text-sm">Registra pagos de clientes en crédito.</p>
                        </a>

                        <a href="{{ route('cajero.devoluciones.index') }}" class="block p-6 bg-purple-500 hover:bg-purple-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Devoluciones</h3>
                            <p class="mt-2 text-sm">Ver Historial y Descargar nota a credito</p>
                        </a>

                        <a href="{{ route('cajero.caja.index') }}" class="block p-6 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Estado de Caja</h3>
                            <p class="mt-2 text-sm">Consulta el movimiento y balance de tu caja.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>