<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Apertura de caja</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 text-gray-900 dark:text-gray-100">
                @if($cajaCerradaHoy)
                    <div class="mb-4 p-4 rounded-lg bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                        <p class="font-semibold">No se puede abrir caja</p>
                        <p>Ya cerró su caja del día ({{ $cajaCerradaHoy->fecha_cierre->format('d/m/Y H:i') }}). Solo se permite una caja por día.</p>
                        <p class="mt-2">Podrá abrir una nueva caja mañana.</p>
                    </div>
                @else
                    @if($errors->any())
                        <div class="mb-4 p-4 rounded-lg bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-4 p-4 rounded-lg bg-blue-50 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                        <p class="font-semibold">Importante</p>
                        <p>Solo se permite abrir una caja por día. Una vez cerrada, no podrá abrir otra caja hasta el día siguiente.</p>
                    </div>

                    <form action="{{ route('cajero.caja.abrir') }}" method="POST">
                        @csrf

                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="monto_inicial">Monto inicial</label>
                                <input id="monto_inicial" name="monto_inicial" type="number" step="0.01" min="0" value="{{ old('monto_inicial') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div class="flex items-center justify-between">
                                <a href="{{ route('cajero.caja.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">Volver al estado de caja</a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 dark:bg-green-700 hover:bg-green-700 dark:hover:bg-green-800 text-white rounded-lg">Abrir caja</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
