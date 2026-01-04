<div class="font-Arima">
    <style>
        .icon::after {
            content: '';
            display: block;
            position: absolute;
            border-top: 17px solid transparent;
            border-bottom: 15px solid transparent;
            border-left: 14px solid rgb(37 99 235 / var(--tw-bg-opacity));
            left: 100%;
            top: 0;
        }
        
        input[type="file"] {
            display: none;
        }
        
        .custom-file-upload {
            border: 1px solid #ccc;
            padding: 6px 12px;
            cursor: pointer;
        }

        select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>

     <button 
        type="button" 
        wire:click="$set('open',true)" 
        wire:loading.attr="disabled"
        class="group cursor-pointer relative inline-flex items-center px-3 py-2 bg-white hover:bg-blue-50 text-blue-600 hover:text-blue-700 rounded-lg border border-blue-200 hover:border-blue-300 shadow-xs hover:shadow-md transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
        title="Ver detalles completos"
    >
        <div class="flex items-center space-x-1">
            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span class="text-xs font-medium">Ver</span>
        </div>
        
        <!-- Indicador sutil de hover -->
        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 group-hover:w-4 h-0.5 bg-blue-500 rounded-full transition-all duration-200"></div>
    </button>

    <x-dialog-modal wire:model="open" maxWidth="3xl">
        <x-slot name="title">
            <div class="flex justify-between">
                <p></p>
                <button type="button" wire:click="close" wire:loading.attr="disabled" class="cursor-pointer py-2.5 px-3 me-2 mb-2 text-sm font-bold text-white focus:outline-none bg-black rounded-full border border-gray-200 hover:bg-gray-100 hover:text-gray-200 focus:z-10 focus:ring-4 focus:ring-gray-100">
                    X
                </button>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="max-w-4xl mx-auto bg-white shadow-2xl rounded-lg overflow-hidden border border-gray-200">
                <!-- Encabezado de la factura -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-700 px-8 py-6 text-gray-800">
                    <div class="flex justify-between items-start">
                        <div>
                            <!-- Información de la empresa -->
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-white">#F-{{ $venta->id }}</div>
                            <p class="text-white text-sm">{{ \Carbon\Carbon::parse($venta->created_at)->format('d/m/Y \\a \\l\\a\\s h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Información del cliente y empresa -->
                <div class="px-8 py-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Información del cliente -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">CLIENTE</h3>
                            <div class="space-y-1 text-gray-600">
                                <p class="font-medium text-gray-900">{{$venta->cliente->nombre}}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalles de los productos -->
                <div class="px-8 py-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">DETALLES DE LA FACTURA</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b-2 border-gray-200">
                                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Producto</th>
                                    <th class="py-3 px-4 text-center text-sm font-semibold text-gray-700">Cantidad</th>
                                    <th class="py-3 px-4 text-right text-sm font-semibold text-gray-700">Precio Unit.</th>
                                    <th class="py-3 px-4 text-right text-sm font-semibold text-gray-700">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($productos as $producto)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 px-4">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $producto->producto->nombre }}</p>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">{{ $producto->cantidad }}</span>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <p class="font-semibold inline">Bs {{ number_format($producto->precio_bolivares, 2, ',', '.') }}</p>
                                            <span class="bg-green-200 text-green-800 text-xs font-medium px-2 rounded-sm border border-green-400">
                                                REF. {{ number_format($producto->precio_dolares, 2, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <p class="font-semibold inline">Bs {{ number_format($producto->precio_bolivares * $producto->cantidad, 2, ',', '.') }}</p>
                                            <span class="bg-green-200 text-green-800 text-xs font-medium px-2 rounded-sm border border-green-400">
                                                REF. {{ number_format($producto->precio_dolares * $producto->cantidad, 2, ',', '.') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totales -->
                <div class="px-8 py-6 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-end">
                        <div class="w-64 space-y-3">
                            <!-- Subtotal calculado -->
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal calculado:</span>
                                <div class="text-right">
                                    <p class="font-semibold">Bs {{ number_format($subtotal_bol, 2, ',', '.') }}</p>
                                    <span class="bg-green-200 text-green-800 text-xs font-medium px-2 rounded-sm border border-green-400">
                                        REF. {{ number_format($subtotal_dol, 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Total guardado en la venta -->

                            <!-- En la sección de totales, modifica: -->
                            <div class="px-8 py-6 bg-gray-50 border-t border-gray-200">
                                <div class="flex justify-end">
                                    <div class="w-80 space-y-3">
                                        <!-- Subtotal -->
                                        <div class="flex justify-between text-gray-600">
                                            <span>Subtotal:</span>
                                            <div class="text-right">
                                                <p class="font-semibold">Bs {{ number_format($subtotal_bol, 2, ',', '.') }}</p>
                                                <span class="bg-green-200 text-green-800 text-xs font-medium px-2 rounded-sm border border-green-400">
                                                    REF. {{ number_format($subtotal_dol, 2, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Desglose: Exento y Gravable -->
                                        @if($exento_bol > 0)
                                            <div class="flex justify-between text-gray-600 text-sm">
                                                <span class="pl-2">→ Exento:</span>
                                                <div class="text-right">
                                                    <p class="font-medium">Bs {{ number_format($exento_bol, 2, ',', '.') }}</p>
                                                    <span class="bg-gray-200 text-gray-800 text-xs px-2 rounded-sm">
                                                        REF. {{ number_format($exento_dol, 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if($gravable_bol > 0)
                                            <div class="flex justify-between text-gray-600 text-sm">
                                                <span class="pl-2">→ Gravable:</span>
                                                <div class="text-right">
                                                    <p class="font-medium">Bs {{ number_format($gravable_bol, 2, ',', '.') }}</p>
                                                    <span class="bg-gray-200 text-gray-800 text-xs px-2 rounded-sm">
                                                        REF. {{ number_format($gravable_dol, 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Mostrar IVA solo si hay -->
                                        @if($iva_bol > 0)
                                            <div class="flex justify-between text-gray-600 border-t border-gray-300 pt-2">
                                                <span>IVA ({{ $porcentaje_iva }}%):</span>
                                                <div class="text-right">
                                                    <p class="font-semibold">Bs {{ number_format($iva_bol, 2, ',', '.') }}</p>
                                                    <span class="bg-green-200 text-green-800 text-xs font-medium px-2 rounded-sm border border-green-400">
                                                        REF. {{ number_format($iva_dol, 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Total -->
                                        <div class="flex justify-between text-lg font-bold text-gray-800 border-t border-gray-300 pt-2">
                                            <span>TOTAL A PAGAR:</span>
                                            <div class="text-right">
                                                <p class="font-semibold">Bs {{ number_format($venta->total_bolivares, 2, ',', '.') }}</p>
                                                <span class="bg-green-200 text-green-800 text-xs font-medium px-2 rounded-sm border border-green-400">
                                                    REF. {{ number_format($venta->total_dolares, 2, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Verificación -->
                                        @php
                                            $total_calculado_bol = $subtotal_bol + $iva_bol;
                                            $diferencia_bs = abs($total_calculado_bol - $venta->total_bolivares);
                                            
                                            $total_calculado_dol = $subtotal_dol + $iva_dol;
                                            $diferencia_dol = abs($total_calculado_dol - $venta->total_dolares);
                                        @endphp
                                        
                                        @if($diferencia_dol > 0.01 || $diferencia_bs > 0.01)
                                            <div class="mt-2 p-2 bg-yellow-100 border border-yellow-400 rounded">
                                                <p class="text-yellow-800 text-sm text-center">
                                                    ⚠️ Diferencia detectada: 
                                                    ${{ number_format($diferencia_dol, 2) }} | 
                                                    Bs {{ number_format($diferencia_bs, 2) }}
                                                </p>
                                            </div>
                                        @endif

                                        @if($this->verificar_deuda() == true)
                                            <p class="bg-red-200 text-red-800 text-xs mt-2 font-medium px-3 py-1 rounded-sm border border-red-400">
                                                DEUDA REGISTRADA - REF. {{ number_format($this->deuda(), 2, ',', '.') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            


                             @if($this->verificar_deuda() == true)
                   
                                <p class="bg-red-200 text-red-800 text-xs mt-2 font-medium px-3 py-1 rounded-sm border border-red-400">
                                    DEUDA REGISTRADA - REF. {{ number_format($this->deuda(), 2, ',', '.') }}
                                </p>


                            @endif

                            <!-- Verificación de consistencia -->
                            @php
                                $diferencia_dol = abs($subtotal_dol - $venta->total_dolares);
                                $diferencia_bs = abs($subtotal_bol - $venta->total_bolivares);
                            @endphp
                            
                            {{-- @if($diferencia_dol > 0.01 || $diferencia_bs > 0.01)
                                <div class="mt-2 p-2 bg-yellow-100 border border-yellow-400 rounded">
                                    <p class="text-yellow-800 text-sm text-center">
                                        ⚠️ Diferencia detectada: 
                                        ${{ number_format($diferencia_dol, 2) }} | 
                                        Bs {{ number_format($diferencia_bs, 2) }}
                                    </p>
                                </div>
                            @endif --}}
                        </div>
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <button type="button" wire:click="close" wire:loading.attr="disabled" class="text-white cursor-pointer bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center me-2 mb-2">
                Cerrar
            </button>
        </x-slot>
    </x-dialog-modal>
</div>