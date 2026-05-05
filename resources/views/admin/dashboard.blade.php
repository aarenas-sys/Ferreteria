<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Panel de Control de Administrador') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("¡Bienvenido al Panel de Administración!") }}
                    <p>Aquí puedes gestionar usuarios, ver informes y supervisar el sistema.</p>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('admin.users.index') }}" class="block p-6 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold">Gestionar Usuarios</h3>
                            <p>Crear, editar y eliminar usuarios del sistema.</p>
                        </a>

                        <a href="{{ route('admin.branches.index') }}" class="block p-6 bg-green-500 hover:bg-green-600 text-white rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold">Gestionar Sucursales</h3>
                            <p>Administrar las sucursales de la ferretería.</p>
                        </a>

                        <a href="{{ route('admin.discounts.index') }}" class="block p-6 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold">Gestionar Descuentos</h3>
                            <p>Crear, habilitar y asignar descuentos por sucursal.</p>
                        </a>

                        <a href="{{ route('admin.settings.index') }}" class="block p-6 bg-purple-500 hover:bg-purple-600 text-white rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold">Configuraciones</h3>
                            <p>Configurar IVA y parámetros globales.</p>
                        </a>

                        <a href="{{ route('reportes.index') }}" class="block p-6 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold">Ver Reportes</h3>
                            <p>Reportes de ventas, inventario y compras.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>