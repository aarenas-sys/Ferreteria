<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pagos de Crédito') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Créditos Pendientes de Pago</h1>
                        <a href="{{ route('cajero') }}" class="bg-gray-500 dark:bg-gray-600 hover:bg-gray-600 dark:hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Volver al Dashboard</a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-500 dark:bg-green-600 border border-green-600 dark:border-green-700 text-white px-6 py-4 rounded-lg mb-6 shadow-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->has('error'))
                        <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-6 py-4 rounded-lg mb-6">
                            {{ $errors->first('error') }}
                        </div>
                    @endif

                    @if($creditos->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-lg rounded-lg overflow-hidden">
                                <thead class="bg-gradient-to-r from-yellow-600 dark:from-yellow-800 to-yellow-700 dark:to-yellow-900 text-white">
                                    <tr>
                                        <th class="px-6 py-4 border-b text-left font-semibold">Cliente</th>
                                        <th class="px-6 py-4 border-b text-left font-semibold">Venta ID</th>
                                        <th class="px-6 py-4 border-b text-right font-semibold">Monto Total</th>
                                        <th class="px-6 py-4 border-b text-right font-semibold">Saldo Pendiente</th>
                                        <th class="px-6 py-4 border-b text-center font-semibold">Fecha Vencimiento</th>
                                        <th class="px-6 py-4 border-b text-center font-semibold">Estado</th>
                                        <th class="px-6 py-4 border-b text-center font-semibold">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($creditos as $credito)
                                    <tr class="hover:bg-yellow-50 dark:hover:bg-gray-700 transition duration-150">
                                        <td class="px-6 py-4 border-b font-medium text-gray-900 dark:text-gray-100">
                                            {{ $credito->cliente->nombre_completo }}
                                        </td>
                                        <td class="px-6 py-4 border-b font-semibold text-blue-600 dark:text-blue-400">
                                            <a href="{{ route('cajero.ventas.show', $credito->venta) }}" class="hover:underline">
                                                #{{ $credito->venta->id }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 border-b text-right font-semibold text-green-600 dark:text-green-400">
                                            ${{ number_format($credito->monto_total, 2) }}
                                        </td>
                                        <td class="px-6 py-4 border-b text-right font-bold text-red-600 dark:text-red-400">
                                            ${{ number_format($credito->saldo_pendiente, 2) }}
                                        </td>
                                        <td class="px-6 py-4 border-b text-center">
                                            @php
                                                $vencido = \Carbon\Carbon::parse($credito->fecha_vencimiento)->isPast();
                                                $class = $vencido ? 'text-red-600 dark:text-red-400 font-bold' : 'text-gray-600 dark:text-gray-400';
                                            @endphp
                                            <span class="{{ $class }}">
                                                {{ \Carbon\Carbon::parse($credito->fecha_vencimiento)->format('d/m/Y') }}
                                                @if($vencido)
                                                    <span class="text-xs bg-red-500 dark:bg-red-600 text-white px-2 py-1 rounded">VENCIDO</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 border-b text-center">
                                            <span class="px-3 py-1 rounded-full text-sm font-bold bg-yellow-500 dark:bg-yellow-700 text-white">
                                                Pendiente
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 border-b text-center">
                                            <button type="button" class="bg-blue-500 dark:bg-blue-700 hover:bg-blue-600 dark:hover:bg-blue-800 text-white px-4 py-2 rounded-md transition duration-200" onclick="togglePayForm({{ $credito->id }})">
                                                Pagar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr id="pay-form-{{ $credito->id }}" class="hidden bg-gray-50 dark:bg-gray-700">
                                        <td colspan="7" class="px-6 py-4">
                                            <form action="{{ route('cajero.ventas.pagar', $credito->venta) }}" method="POST" class="flex gap-4 items-center">
                                                @csrf
                                                @method('PATCH')
                                                <div class="flex-1 max-w-xs">
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto a pagar:</label>
                                                    <input type="number" name="monto" step="0.01" min="0.01" max="{{ $credito->saldo_pendiente }}" value="{{ $credito->saldo_pendiente }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </div>
                                                <div class="flex gap-2 pt-6">
                                                    <button type="submit" class="bg-green-500 dark:bg-green-700 hover:bg-green-600 dark:hover:bg-green-800 text-white px-5 py-2 rounded-md font-semibold">Confirmar Pago</button>
                                                    <button type="button" class="bg-gray-500 dark:bg-gray-600 hover:bg-gray-600 dark:hover:bg-gray-700 text-white px-5 py-2 rounded-md" onclick="togglePayForm({{ $credito->id }})">Cancelar</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $creditos->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">No hay créditos pendientes</h3>
                            <p class="text-gray-600 dark:text-gray-400">¡Todos los créditos están pagados!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePayForm(creditoId) {
            const form = document.getElementById('pay-form-' + creditoId);
            form.classList.toggle('hidden');
        }
    </script>
</x-app-layout>
