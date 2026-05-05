<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detalles de la Sucursal
            </h2>
            <a href="{{ route('admin.branches.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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
                            <strong>Nombre:</strong> {{ $branch->name }}
                        </div>
                        <div>
                            <strong>Dirección:</strong> {{ $branch->address }}
                        </div>
                        <div>
                            <strong>Teléfono:</strong> {{ $branch->phone ?? 'N/A' }}
                        </div>
                        <div>
                            <strong>Usuarios asociados:</strong> {{ $branch->users->count() }}
                        </div>
                        <div>
                            <strong>Creado:</strong> {{ $branch->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div>
                            <strong>Actualizado:</strong> {{ $branch->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    @if($branch->users->count() > 0)
                        <div class="mt-6">
                            <h3 class="text-lg font-medium mb-2">Usuarios asociados:</h3>
                            <ul class="list-disc list-inside">
                                @foreach($branch->users as $user)
                                    <li>{{ $user->name }} ({{ $user->email }}) - {{ ucfirst($user->role) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>