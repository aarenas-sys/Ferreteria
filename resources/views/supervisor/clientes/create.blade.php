<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Crear Cliente
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Registra un nuevo cliente en el sistema.</p>
            </div>
            <a href="{{ route('supervisor.clientes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('supervisor.clientes.store') }}" method="POST">
                    @csrf

                    <div class="p-6 space-y-6">
                        <!-- Información Personal -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Información Personal</h3>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="primer_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primer Nombre *</label>
                                    <input type="text" name="primer_nombre" id="primer_nombre" value="{{ old('primer_nombre') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                    @error('primer_nombre')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="segundo_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Segundo Nombre</label>
                                    <input type="text" name="segundo_nombre" id="segundo_nombre" value="{{ old('segundo_nombre') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                    @error('segundo_nombre')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="primer_apellido" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primer Apellido *</label>
                                    <input type="text" name="primer_apellido" id="primer_apellido" value="{{ old('primer_apellido') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                    @error('primer_apellido')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="segundo_apellido" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Segundo Apellido</label>
                                    <input type="text" name="segundo_apellido" id="segundo_apellido" value="{{ old('segundo_apellido') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                    @error('segundo_apellido')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="documento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Documento *</label>
                                    <input type="text" name="documento" id="documento" value="{{ old('documento') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                    @error('documento')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono *</label>
                                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                    @error('telefono')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6">
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mt-6">
                                <label for="direccion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección *</label>
                                <textarea name="direccion" id="direccion" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('direccion') }}</textarea>
                                @error('direccion')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Información de Crédito -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Información de Crédito</h3>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="cupo_credito" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cupo de Crédito ($)</label>
                                    <input type="number" name="cupo_credito" id="cupo_credito" value="{{ old('cupo_credito') }}" min="0" max="1000000" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                    <p class="mt-1 text-sm text-gray-500">Máximo $1.000.000</p>
                                    @error('cupo_credito')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="estado_credito" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado de Crédito</label>
                                    <select name="estado_credito" id="estado_credito" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                        <option value="activo" {{ old('estado_credito', 'activo') === 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="bloqueado" {{ old('estado_credito') === 'bloqueado' ? 'selected' : '' }}>Bloqueado</option>
                                    </select>
                                    @error('estado_credito')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                        <a href="{{ route('supervisor.clientes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Cancelar</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Crear Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>