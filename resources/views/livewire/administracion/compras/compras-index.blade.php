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
                        <h2 class="text-xl font-bold text-white">Compras Registradas</h2>
                    </div>
                    <span class="bg-white text-blue-600 px-3 py-1 rounded-full text-sm font-bold">
                        {{ $registros->count() }} compras
                    </span>
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
                                Producto
                            </th>
                            <th scope="col" class="px-6 py-4 text-center font-semibold text-gray-700">
                                Cantidad
                            </th>
                            <th scope="col" class="px-6 py-4 text-center font-semibold text-gray-700">
                                Método de pago
                            </th>

                            <th scope="col" class="px-6 py-4 text-center font-semibold text-gray-700">
                                Precio por unidad
                            </th>

                             <th  class="px-6 py-3 text-sm lg:text-md">
                                Total pagado
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
                                    
                                   {{$registro->producto->nombre}}
                                </span>
                         </td>

                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg font-medium">
                                    
                                   {{$registro->cantidad}}
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
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg font-medium">
                                    
                                    @if($registro->precio_compra_dolares)
                                    {{$registro->precio_compra_dolares}} $
                                    @endif
                                    @if($registro->precio_compra_bolivares)
                                    {{$registro->precio_compra_bolivares}} Bs
                                    @endif
                            
                                </span>
                        </td>

                         <td class="px-6 py-4 text-center">
                                 <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg font-medium">
                                    
                                    @if($registro->total_pagado_dolares)
                                    {{$registro->total_pagado_dolares}} $
                                    @endif
                                    @if($registro->total_pagado_bolivares)
                                    {{$registro->total_pagado_bolivares}} Bs
                                    @endif
                            
                                </span>
                        </td>

                         <td class="px-6 py-4 text-center">

                                @livewire('administracion.compras.compras-edit', ['registro' => $registro],key(01.,'$registro->id'))
                                 
                    
                        </td>


                        </tr>
                        @endforeach
                    </tbody>
                </table>
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

            <p class="text-gray-600 mb-6 text-lg">No hay compras registradas</p>
          
        </div>
    </div>
    @endif
</div>

