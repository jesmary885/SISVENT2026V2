<div class="w-full p-4 bg-gradient-to-br bg-gray-200 ">
    <!-- Header Mejorado -->


    @if($registros->count())
    <!-- Tabla Mejorada -->
    <div class=" mx-auto">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            <!-- Header de la tabla -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-list text-white text-xl mr-3"></i>
                        <h2 class="text-xl font-bold text-white">Ventas Registradas</h2>
                    </div>
                    <span class="bg-white text-blue-600 px-3 py-1 rounded-full text-sm font-bold">
                        {{ $registros->count() }} ventas
                    </span>
                     <!-- Información de paginación -->
                
                </div>
            </div>

            <div class="bg-blue-50 px-6 py-3 border-b border-blue-100">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                        <p class="text-sm text-blue-700 font-medium">
                            Mostrando 
                            <span class="font-bold">{{ $registros->firstItem() }}</span> 
                            a 
                            <span class="font-bold">{{ $registros->lastItem() }}</span> 
                            de 
                            <span class="font-bold">{{ $registros->total() }}</span> 
                            ventas
                        </p>
                        
                        <!-- Selector de items por página -->
                        <div class="flex items-center space-x-2">
                            <label for="perPage" class="text-sm text-blue-700 font-medium">Mostrar:</label>
                            <select 
                                wire:model.live="perPage" 
                                id="perPage"
                                class="border border-blue-300 rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>

                            <th scope="col" class="px-6 py-4 text-left font-semibold text-gray-700">
                                Fecha
                            </th>
                            <th scope="col" class="px-6 py-4 text-center font-semibold text-gray-700">
                                Cant. de productos
                            </th>
                            <th scope="col" class="px-6 py-4 text-center font-semibold text-gray-700">
                                Método de pago
                            </th>

                            <th scope="col" class="px-6 py-4 text-center font-semibold text-gray-700">
                                Total
                            </th>

                             <th  class="px-6 py-3 text-sm lg:text-md">
                            
                            </th>
  


                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($registros as $registro)
                        <tr class="hover:bg-blue-50 transition-colors duration-200 group">

                              <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-calendar-alt text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 group-hover:text-blue-600">    {{$registro->created_at}}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg font-medium">
                                    
                                   {{$this->cant_productos($registro)}}
                                </span>
                        </td>
                            
                         <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg font-medium">
                                    
                                    @if($registro->metodo_pago == 'pago_movil')
                                    Pago móvil
                                    @endif
                                      @if($registro->metodo_pago == 'debito')
                                    Débito
                                    @endif
                                    @if($registro->metodo_pago == 'dol_efec')
                                    Dólares en efectivo
                                    @endif
                                    @if($registro->metodo_pago == 'bs_efec')
                                    Bolívares en efectivo
                                    @endif
                                    @if($registro->metodo_pago == 'USDT')
                                    usdt
                                    @endif
                                </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <div class="space-y-1">
                                <div class="bg-blue-100 text-blue-800 px-3 py-2 rounded-lg">
                                    <!-- Mostrar desglose del total -->
                                    <div class="text-xs text-left space-y-1">
                                        @php
                                            $subtotal_bol = $this->subtotal_bol($registro);
                                            $iva_bol = $this->iva_bolivares($registro);
                                            $exento_bol = $this->exento_bolivares($registro);
                                            $porcentaje_iva = $this->negocio_config ? $this->negocio_config->porcentaje_iva : 16;
                                        @endphp
                                        
                                        @if($exento_bol > 0)
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Exento:</span>
                                                <span class="font-medium">Bs {{ number_format($exento_bol, 2, ',', '.') }}</span>
                                            </div>
                                        @endif
                                        
                                        @if($iva_bol > 0)
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Gravable:</span>
                                                <span class="font-medium">Bs {{ number_format($subtotal_bol - $exento_bol, 2, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">IVA ({{ $porcentaje_iva }}%):</span>
                                                <span class="font-medium">Bs {{ number_format($iva_bol, 2, ',', '.') }}</span>
                                            </div>
                                        @endif
                                        
                                        <div class="flex justify-between font-bold border-t border-blue-300 pt-1 mt-1">
                                            <span>TOTAL:</span>
                                            <span>Bs {{ number_format($this->total_bolivares($registro), 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Referencia en dólares -->
                                    <div class="mt-2 text-xs">
                                        <span class="bg-green-200 text-green-800 px-2 py-1 rounded-sm border border-green-400">
                                            REF. {{ number_format($this->total_dolares($registro), 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                @if($this->verificar_deuda($registro) == true)
                                    <p class="bg-red-200 text-red-800 text-xs mt-2 font-medium px-3 py-1 rounded-sm border border-red-400">
                                        DEUDA REGISTRADA - REF. {{ number_format($this->deuda($registro), 2, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">

                            @livewire('ventas.venta-view', ['venta' => $registro],key(01.,'$registro->id'))
                                 
                    
                        </td>

                        <td class="px-6 py-4 text-center">

                            <button
                                wire:click="delete('{{$registro->id}}')"
                                class="w-10 h-10 bg-red-500 cursor-pointer hover:bg-red-600 text-white rounded-lg flex items-center justify-center transition-all duration-300 transform hover:scale-110"
                                title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>

                        </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

              <!-- Paginación -->
            <div class="bg-white px-6 py-4 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                    <!-- Información de página actual -->
                    <p class="text-sm text-gray-700">
                        Página <span class="font-bold">{{ $registros->currentPage() }}</span> 
                        de <span class="font-bold">{{ $registros->lastPage() }}</span>
                    </p>
                    
                    <!-- Navegación -->
                    <nav class="flex items-center space-x-1">
                        <!-- Primera página -->
                        <button 
                            wire:click="gotoPage(1)"
                            @if($registros->onFirstPage()) disabled @endif
                            class="px-3 py-1 rounded-lg border border-gray-300 text-sm font-medium 
                                   @if($registros->onFirstPage()) 
                                       bg-gray-100 text-gray-400 cursor-not-allowed 
                                   @else 
                                       bg-white text-gray-700 hover:bg-blue-50 hover:text-blue-600 
                                   @endif"
                        >
                            <i class="fas fa-angle-double-left"></i>
                        </button>

                        <!-- Página anterior -->
                        <button 
                            wire:click="previousPage"
                            @if($registros->onFirstPage()) disabled @endif
                            class="px-3 py-1 rounded-lg border border-gray-300 text-sm font-medium 
                                   @if($registros->onFirstPage()) 
                                       bg-gray-100 text-gray-400 cursor-not-allowed 
                                   @else 
                                       bg-white text-gray-700 hover:bg-blue-50 hover:text-blue-600 
                                   @endif"
                        >
                            <i class="fas fa-angle-left"></i>
                        </button>

                        <!-- Números de página -->
                        @foreach($registros->getUrlRange(max(1, $registros->currentPage() - 2), min($registros->lastPage(), $registros->currentPage() + 2)) as $page => $url)
                            <button 
                                wire:click="gotoPage({{ $page }})"
                                class="px-3 py-1 rounded-lg border text-sm font-medium 
                                       @if($page == $registros->currentPage()) 
                                           bg-blue-600 text-white border-blue-600 
                                       @else 
                                           bg-white text-gray-700 border-gray-300 hover:bg-blue-50 hover:text-blue-600 
                                       @endif"
                            >
                                {{ $page }}
                            </button>
                        @endforeach

                        <!-- Página siguiente -->
                        <button 
                            wire:click="nextPage"
                            @if($registros->hasMorePages()) @else disabled @endif
                            class="px-3 py-1 rounded-lg border border-gray-300 text-sm font-medium 
                                   @if(!$registros->hasMorePages()) 
                                       bg-gray-100 text-gray-400 cursor-not-allowed 
                                   @else 
                                       bg-white text-gray-700 hover:bg-blue-50 hover:text-blue-600 
                                   @endif"
                        >
                            <i class="fas fa-angle-right"></i>
                        </button>

                        <!-- Última página -->
                        <button 
                            wire:click="gotoPage({{ $registros->lastPage() }})"
                            @if($registros->currentPage() == $registros->lastPage()) disabled @endif
                            class="px-3 py-1 rounded-lg border border-gray-300 text-sm font-medium 
                                   @if($registros->currentPage() == $registros->lastPage()) 
                                       bg-gray-100 text-gray-400 cursor-not-allowed 
                                   @else 
                                       bg-white text-gray-700 hover:bg-blue-50 hover:text-blue-600 
                                   @endif"
                        >
                            <i class="fas fa-angle-double-right"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Estado vacío mejorado -->
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl p-12 text-center border border-gray-200">
            <div class="w-24 h-24 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-box-open text-gray-500 text-3xl"></i>
            </div>

            <p class="text-gray-600 mb-6 text-lg">No hay ventas registradas</p>
          
        </div>
    </div>
    @endif
</div>








