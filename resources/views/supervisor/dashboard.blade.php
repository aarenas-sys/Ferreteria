<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Panel del Supervisor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("¡Bienvenido al Panel del Supervisor!") }}
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Gestiona inventario, proveedores, compras y visualiza reportes operacionales.</p>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('supervisor.productos.index') }}" class="block p-6 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Gestionar Inventario</h3>
                            <p class="mt-2 text-sm">Administra productos y controla niveles de stock.</p>
                        </a>

                        <a href="{{ route('supervisor.proveedores.index') }}" class="block p-6 bg-green-500 hover:bg-green-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Gestionar Proveedores</h3>
                            <p class="mt-2 text-sm">Administra información de proveedores y contactos.</p>
                        </a>

                        <a href="{{ route('supervisor.compras.index') }}" class="block p-6 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Gestionar Compras</h3>
                            <p class="mt-2 text-sm">Crea y gestiona órdenes de compra con proveedores.</p>
                        </a>

                        <a href="{{ route('supervisor.clientes.index') }}" class="block p-6 bg-purple-500 hover:bg-purple-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Gestionar Clientes</h3>
                            <p class="mt-2 text-sm">Mantén registro de clientes y sus datos de contacto.</p>
                        </a>

                        <a href="{{ route('reportes.index') }}" class="block p-6 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Ver Reportes</h3>
                            <p class="mt-2 text-sm">Consulta reportes de ventas, compras e inventario.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>