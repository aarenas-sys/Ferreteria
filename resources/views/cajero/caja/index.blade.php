<x-app-layout>
    @php
        $cajaAbierta = $cajaAbierta ?? null;
        $cajaCerradaHoy = $cajaCerradaHoy ?? null;
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Estado de caja</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 rounded-lg bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(isset($cajaAbierta) && $cajaAbierta)
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="p-4 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Monto inicial</p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">${{ number_format($cajaAbierta->monto_inicial, 2) }}</p>
                            </div>
                            <div class="p-4 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Apertura</p>
                                <p class="mt-2 text-lg text-gray-900 dark:text-gray-100">{{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="p-4 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Estado</p>
                                <p class="mt-2 text-lg capitalize text-indigo-600 dark:text-indigo-400">{{ $cajaAbierta->estado }}</p>
                            </div>
                            <div class="p-4 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sucursal</p>
                                <p class="mt-2 text-lg text-gray-900 dark:text-gray-100">{{ auth()->user()->branch?->name ?? 'Sin sucursal' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('cajero.caja.arqueo.form') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 text-white rounded-lg">Realizar arqueo</a>
                            <a href="{{ route('cajero.caja.cierre.form') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 dark:bg-yellow-700 hover:bg-yellow-700 dark:hover:bg-yellow-800 text-white rounded-lg">Preparar cierre</a>
                            <form action="{{ route('cajero.caja.cerrar') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-700 hover:bg-red-700 dark:hover:bg-red-800 text-white rounded-lg">Cerrar caja</button>
                            </form>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-lg text-gray-700 dark:text-gray-300">No hay una caja abierta en este momento.</p>
                            @if(isset($cajaCerradaHoy) && $cajaCerradaHoy)
                                <div class="mt-4 rounded-lg border border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900 p-4 text-red-900 dark:text-red-200">
                                    <p class="font-semibold">Ya cerró su caja hoy.</p>
                                    <p class="mt-2">Solo se permite abrir una nueva caja una vez al día por cada cajero.</p>
                                    <p class="mt-1 text-sm">Último cierre: {{ $cajaCerradaHoy->fecha_cierre->format('d/m/Y H:i') }}</p>
                                </div>
                            @else
                                <a href="{{ route('cajero.caja.abrir.form') }}" class="mt-6 inline-flex items-center px-6 py-3 bg-green-600 dark:bg-green-700 hover:bg-green-700 dark:hover:bg-green-800 text-white rounded-lg">Abrir nueva caja</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
