<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestión de Sucursales
            </h2>
            <a href="{{ route('admin.branches.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Crear Sucursal
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <th class="px-4 py-2 text-left">Nombre</th>
                                    <th class="px-4 py-2 text-left">Dirección</th>
                                    <th class="px-4 py-2 text-left">Teléfono</th>
                                    <th class="px-4 py-2 text-left">Usuarios</th>
                                    <th class="px-4 py-2 text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branches as $branch)
                                    <tr class="border-b dark:border-gray-600">
                                        <td class="px-4 py-2">{{ $branch->name }}</td>
                                        <td class="px-4 py-2">{{ $branch->address }}</td>
                                        <td class="px-4 py-2">{{ $branch->phone ?? 'N/A' }}</td>
                                        <td class="px-4 py-2">{{ $branch->users_count }}</td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('admin.branches.show', $branch) }}" class="text-blue-500 hover:text-blue-700 mr-2">Ver</a>
                                            <a href="{{ route('admin.branches.edit', $branch) }}" class="text-yellow-500 hover:text-yellow-700 mr-2">Editar</a>
                                            <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $branches->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>