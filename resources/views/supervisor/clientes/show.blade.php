<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Detalles del Cliente
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $cliente->nombre_completo }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('supervisor.clientes.edit', $cliente) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Editar
                </a>
                <a href="{{ route('supervisor.clientes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Volver al listado
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('success'))
                        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Información Personal -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Información Personal</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre Completo</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $cliente->nombre_completo }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Documento</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $cliente->documento }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $cliente->email ?? 'No especificado' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $cliente->telefono }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dirección</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $cliente->direccion }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Información de Crédito -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Información de Crédito</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cupo de Crédito</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $cliente->cupo_credito ? '$' . number_format($cliente->cupo_credito, 2) : 'No asignado' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Saldo Actual</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($cliente->saldo_actual, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                                    <dd class="text-sm">
                                        @if($cliente->estaActivo())
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-sm font-medium text-green-800">Activo</span>
                                        @elseif($cliente->estaEnMora())
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-sm font-medium text-red-800">En mora</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-sm font-medium text-gray-800">Bloqueado</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            @if($cliente->estaEnMora())
                                <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                                Cliente en mora
                                            </h3>
                                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                                <p>Este cliente tiene deuda vencida y debe regularizar su situación.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>