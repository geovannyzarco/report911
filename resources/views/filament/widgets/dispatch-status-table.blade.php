<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $heading }}
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 font-medium text-gray-500 dark:text-gray-400">
                            Estado
                        </th>
                        <th class="px-6 py-3 font-medium text-gray-500 dark:text-gray-400 text-right">
                            Cantidad
                        </th>
                        <th class="px-6 py-3 font-medium text-gray-500 dark:text-gray-400 text-right">
                            Porcentaje
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estados as $fila)
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
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-6 py-4">
                                <x-filament::badge :color="$color">
                                    {{ $fila->Estado }}
                                </x-filament::badge>
                            </td>
                            <td class="px-6 py-4 font-bold text-right">
                                {{ number_format($fila->Cantidad) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                {{ $porcentaje }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-50 dark:bg-gray-700">
                        <td class="px-6 py-4">
                            TOTAL
                        </td>
                        <td class="px-6 py-4 text-right">
                            {{ number_format($total) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            100%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
