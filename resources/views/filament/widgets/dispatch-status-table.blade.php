<div>
    <x-filament::section>
        <x-slot name="heading">
            {{ $heading }}
        </x-slot>

        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            #
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            Cantidad
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            Porcentaje
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 w-1/3">
                            Distribucion
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($estados as $index => $fila)
                        @php
                            $porcentaje = $total > 0 ? round(($fila->Cantidad / $total) * 100, 1) : 0;
                            $color = match($fila->Estado) {
                                'Cerrado' => 'success',
                                'Terminado' => 'info',
                                'En Sitio' => 'danger',
                                'En Ruta' => 'warning',
                                'Req_Despacho' => 'gray',
                                'Despachado' => 'info',
                                default => 'gray',
                            };
                            $bgColor = match($fila->Estado) {
                                'Cerrado' => 'bg-green-500',
                                'Terminado' => 'bg-blue-500',
                                'En Sitio' => 'bg-red-500',
                                'En Ruta' => 'bg-yellow-500',
                                'Req_Despacho' => 'bg-gray-400',
                                'Despachado' => 'bg-blue-400',
                                default => 'bg-gray-400',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 font-medium">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-6 py-4">
                                <x-filament::badge :color="$color" size="md">
                                    {{ $fila->Estado }}
                                </x-filament::badge>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                {{ number_format($fila->Cantidad) }}
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-700 dark:text-gray-300">
                                {{ $porcentaje }}%
                            </td>
                            <td class="px-6 py-4">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                                    <div class="{{ $bgColor }} h-3 rounded-full transition-all duration-500"
                                         style="width: {{ $porcentaje }}%">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600">
                        <td class="px-6 py-4">
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white uppercase text-xs tracking-wider">
                            Total
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-lg text-gray-900 dark:text-white">
                            {{ number_format($total) }}
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                            100%
                        </td>
                        <td class="px-6 py-4">
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>
</div>
