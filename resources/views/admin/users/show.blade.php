<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detalles del Usuario
            </h2>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <strong>Nombre completo:</strong> {{ $user->name }}
                        </div>
                        <div>
                            <strong>Primer Nombre:</strong> {{ $user->first_name }}
                        </div>
                        <div>
                            <strong>Segundo Nombre:</strong> {{ $user->middle_name ?: 'N/A' }}
                        </div>
                        <div>
                            <strong>Primer Apellido:</strong> {{ $user->last_name }}
                        </div>
                        <div>
                            <strong>Segundo Apellido:</strong> {{ $user->second_last_name ?: 'N/A' }}
                        </div>
                        <div>
                            <strong>Email:</strong> {{ $user->email }}
                        </div>
                        <div>
                            <strong>Rol:</strong> {{ ucfirst($user->role) }}
                        </div>
                        <div>
                            <strong>Sucursal:</strong> {{ $user->branch ? $user->branch->name : 'N/A' }}
                        </div>
                        <div>
                            <strong>Creado:</strong> {{ $user->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div>
                            <strong>Actualizado:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>