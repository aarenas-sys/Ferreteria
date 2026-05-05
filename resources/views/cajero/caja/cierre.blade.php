<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Cierre de caja</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-6 text-gray-900 dark:text-gray-100">
                @if(session('success'))
                    <div class="p-4 rounded-lg bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="p-4 rounded-lg bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="p-4 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total sistema</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($totales['total_sistema'], 2) }}</p>
                    </div>
                    <div class="p-4 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Monto real</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($caja->monto_real ?? 0, 2) }}</p>
                    </div>
                    <div class="p-4 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Diferencia</p>
                        @php $diferencia = $caja->diferencia ?? ($caja->monto_real - $totales['total_sistema']); @endphp
                        <p class="mt-2 text-3xl font-bold {{ $diferencia === 0 ? 'text-green-600 dark:text-green-400' : ($diferencia > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400') }}">
                            ${{ number_format($diferencia, 2) }}
                        </p>
                    </div>
                </div>

                <div class="border-t border-gray-300 dark:border-gray-700 pt-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Caja abierta desde</p>
                    <p class="mt-2 text-lg text-gray-900 dark:text-gray-100">{{ $caja->fecha_apertura->format('d/m/Y H:i') }}</p>
                </div>

                <form action="{{ route('cajero.caja.cerrar') }}" method="POST" class="mt-6">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 dark:bg-yellow-700 hover:bg-yellow-700 dark:hover:bg-yellow-800 text-white rounded-lg">Cerrar caja</button>
                    <a href="{{ route('cajero.caja.index') }}" class="ml-4 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">Volver</a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
