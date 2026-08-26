<div>
    <x-filament::section>
        <x-slot name="heading">
            {{ $heading }}
        </x-slot>

        <div class="filament-tables-container">
            <div class="overflow-x-auto">
                <table class="filament-tables-table w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-12">
                                #
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Estado
                            </th>
                            <th scope="col" class="px-6 py-3 text-right">
                                Cantidad
                            </th>
                            <th scope="col" class="px-6 py-3 text-right">
                                Porcentaje
                            </th>
                        </tr>
                    </thead>
                    <tbody>
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
                            @endphp
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="px-6 py-4">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    <x-filament::badge :color="$color">
                                        {{ $fila->Estado }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                    {{ number_format($fila->Cantidad) }}
                                </td>
                                <td class="px-6 py-4 text-right text-gray-900 dark:text-white">
                                    {{ $porcentaje }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border-t-2 border-gray-200 dark:border-gray-600">
                            <td class="px-6 py-4">
                            </td>
                            <td class="px-6 py-4 uppercase text-xs tracking-wider">
                                Total
                            </td>
                            <td class="px-6 py-4 text-right text-lg">
                                {{ number_format($total) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                100%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </x-filament::section>
</div>
