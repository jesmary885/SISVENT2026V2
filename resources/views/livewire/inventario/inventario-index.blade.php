<div class="w-full p-4 bg-gradient-to-br bg-gray-200 ">
    <!-- Header Mejorado -->


    <!-- Barra de Búsqueda Mejorada -->
    <div class=" mx-auto mb-8">
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-200">
            <div class="flex flex-col lg:flex-row gap-4 items-center">
                <!-- Buscador -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input 
                        wire:model="search"
                        type="text" 
                        class="w-full pl-10 pr-4 py-4 bg-gray-100 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg transition-all duration-300 placeholder-gray-500 shadow-sm"
                        placeholder="Buscar producto por nombre..."
                    />
                </div>
                
                <!-- Botón Agregar -->

                @can('Administrador')
                <div class="w-full lg:w-auto">
                    @livewire('inventario.inventario-create', ['tipo' => 'agregar'])
                </div>

                <div class=" w-full lg:w-auto">

                     @livewire('inventario.import-export-inventario')

                </div>
                @endcan
            </div>
        </div>
    </div>

   @if($registros->count())
    <!-- Tabla Mejorada -->
    <div class="mx-auto">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            <!-- Header de la tabla -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-list text-white text-xl mr-3"></i>
                        <h2 class="text-xl font-bold text-white">Productos Registrados</h2>
                    </div>
                    <span class="bg-white text-blue-600 px-3 py-1 rounded-full text-sm font-bold">
                        {{ $registros->total() }} productos totales
                    </span>
                </div>
            </div>

            <!-- Información de paginación -->
            <div class="bg-blue-50 px-6 py-3 border-b border-blue-100">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                    <p class="text-sm text-blue-700 font-medium">
                        Mostrando 
                        <span class="font-bold">{{ $registros->firstItem() }}</span> 
                        a 
                        <span class="font-bold">{{ $registros->lastItem() }}</span> 
                        de 
                        <span class="font-bold">{{ $registros->total() }}</span> 
                        productos
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
                            <th class="px-6 py-4 text-left font-semibold text-gray-700">Producto</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-700">Stock</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-700">Código</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-700">Marca</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-700">Precio</th>
                            @can('Administrador')
                            <th class="px-6 py-4 text-center font-semibold text-gray-700">Acciones</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($registros as $registro)
                        <tr class="hover:bg-blue-50 transition-colors duration-200 group">
                            <!-- Producto -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-box text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 group-hover:text-blue-600">{{ $registro->nombre }}</p>
                                        <p class="text-sm text-gray-500">ID: {{ $registro->id }}</p>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Stock -->
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold 
                                    {{ $registro->cantidad > 20 ? 'bg-green-100 text-green-800' : 
                                       ($registro->cantidad > 5 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    <i class="fas fa-cubes mr-2"></i>
                                    {{ $registro->cantidad }} unidades
                                </span>
                            </td>
                            
                            <!-- Código -->
                            <td class="px-6 py-4 text-center">
                                <code class="bg-gray-100 text-gray-800 px-3 py-1 rounded-lg font-mono text-sm">
                                    {{ $registro->cod_barra }}
                                </code>
                            </td>
                            
                            <!-- Marca -->
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg font-medium">
                                    {{ $registro->marca->nombre }}
                                </span>
                            </td>
                            
                            <!-- Precio -->
                            <td class="px-6 py-4 text-center">
                                <p class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-3 py-2 rounded-lg font-bold text-sm">
                                    Bs {{$this->total_venta_bs($registro->precio_venta)}}
                                    <span class="bg-green-200 text-green-800 text-xs font-medium px-2 rounded-sm dark:bg-gray-700 dark:text-green-400 border border-green-400"> 
                                        ${{ number_format($registro->precio_venta, 2) }}
                                    </span>
                                </p>
                            </td>
                            
                            <!-- Acciones -->

                            @can('Administrador')
                            <td class="px-6 py-4">
                                <div class="flex justify-center space-x-2">
                                    @livewire('inventario.inventario-add', ['registro' => $registro], key('add-'.$registro->id))
                                    
                                    @livewire('inventario.inventario-create', ['registro' => $registro,'tipo' => 'editar'], key('edit-'.$registro->id))
                                    
                                    @livewire('inventario.inventario-barcode', ['registro' => $registro], key('barcode-'.$registro->id))
                                    
                                    <button
                                        wire:click="delete('{{$registro->id}}')"
                                        class="w-10 h-10 bg-red-500 cursor-pointer hover:bg-red-600 text-white rounded-lg flex items-center justify-center transition-all duration-300 transform hover:scale-110"
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                            @endcan
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
            <h3 class="text-2xl font-bold text-gray-800 mb-3">Inventario Vacío</h3>
            <p class="text-gray-600 mb-6 text-lg">No hay productos registrados en el sistema. Comienza agregando tu primer producto.</p>
            <div class="flex justify-center">
                @livewire('inventario.inventario-create', ['tipo' => 'agregar'])
            </div>
        </div>
    </div>
@endif
</div>