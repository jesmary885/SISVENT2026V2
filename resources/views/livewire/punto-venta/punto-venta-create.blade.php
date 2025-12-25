<div>
    <style>
        .checkbox:checked {
            right: 0;
        }
        .checkbox:checked + .toggle-label {
            background-color: #4c51bf;
        }
    </style>

    <!-- NOTIFICACIONES -->
    <div x-data="{ show: false, message: '', type: '' }" 
         x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed top-4 right-4 z-50 max-w-sm"
         x-cloak>
        <div x-bind:class="{
            'bg-green-500': type === 'success',
            'bg-red-500': type === 'error', 
            'bg-yellow-500': type === 'warning',
            'bg-blue-500': type === 'info'
        }" class="text-white px-6 py-3 rounded-lg shadow-lg">
            <span x-text="message"></span>
        </div>
    </div>

    <!-- MODAL PARA DATOS DE CLIENTE AL PAUSAR -->
    @if($mostrar_modal_pausa)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Datos del Cliente</h3>
            <p class="text-gray-600 mb-4">Para pausar la venta, ingrese los datos del cliente:</p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nombre *</label>
                    <input 
                        type="text"
                        wire:model="nombre_cliente_pausa"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Nombre del cliente"
                        autofocus
                    >
                    @error('nombre_cliente_pausa') 
                        <span class="text-red-500 text-xs">{{ $message }}</span> 
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Teléfono</label>
                    <input 
                        type="text"
                        wire:model="telefono_cliente_pausa"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Número de teléfono"
                    >
                </div>
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <button 
                    wire:click="cancelarPausa"
                    class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 text-gray-700"
                >
                    Cancelar
                </button>
                <button 
                    wire:click="procesarPausaVenta"
                    wire:loading.attr="disabled"
                    wire:target="procesarPausaVenta"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
                    @if(empty($nombre_cliente_pausa)) disabled @endif
                >
                    <span wire:loading.remove wire:target="procesarPausaVenta">
                        Pausar Venta
                    </span>
                    <span wire:loading wire:target="procesarPausaVenta">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif


        <!-- MODAL BUSCADOR DE CLIENTES -->
    @if($mostrar_buscador_cliente)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Seleccionar Cliente</h3>
                <button 
                    wire:click="$set('mostrar_buscador_cliente', false)"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- BUSCADOR -->
            <div class="mb-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input 
                        type="text"
                        wire:model.debounce.300ms="search_cliente"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Buscar por nombre, teléfono o email..."
                        autofocus
                    >
                </div>
            </div>

            <!-- LISTA DE CLIENTES ENCONTRADOS -->
            @if(!$mostrar_form_nuevo_cliente)
            <div class="max-h-96 overflow-y-auto">
                @if(count($clientes_encontrados) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($clientes_encontrados as $cliente)
                    <div class="border border-gray-200 rounded p-3 hover:bg-gray-50 cursor-pointer transition"
                         wire:click="seleccionarCliente({{ $cliente->id }})">
                        <div class="font-medium text-gray-800">{{ $cliente->nombre }}</div>
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-phone mr-1"></i>{{ $cliente->telefono }}
                        </div>
                        @if($cliente->email)
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-envelope mr-1"></i>{{ $cliente->email }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-users text-3xl mb-3"></i>
                    <p>No se encontraron clientes</p>
                </div>
                @endif
                
                <!-- BOTÓN PARA AGREGAR NUEVO CLIENTE -->
                <div class="mt-4 text-center">
                    <button 
                        wire:click="mostrarFormNuevoCliente"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    >
                        <i class="fas fa-plus mr-2"></i>Agregar Nuevo Cliente
                    </button>
                </div>
            </div>
            @endif

            <!-- FORMULARIO PARA NUEVO CLIENTE -->
            @if($mostrar_form_nuevo_cliente)
            <div class="border-t pt-4 mt-4">
                <h4 class="font-bold mb-3">Nuevo Cliente</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nombre *</label>
                        <input 
                            type="text"
                            wire:model="nuevo_cliente_nombre"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Nombre completo"
                        >
                        @error('nuevo_cliente_nombre') 
                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Teléfono *</label>
                        <input 
                            type="text"
                            wire:model="nuevo_cliente_telefono"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Número de teléfono"
                        >
                        @error('nuevo_cliente_telefono') 
                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input 
                            type="email"
                            wire:model="nuevo_cliente_email"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Correo electrónico"
                        >
                        @error('nuevo_cliente_email') 
                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-6">
                    <button 
                        wire:click="$set('mostrar_form_nuevo_cliente', false)"
                        class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100"
                    >
                        Cancelar
                    </button>
                    <button 
                        wire:click="crearNuevoCliente"
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
                    >
                        Guardar Cliente
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- CLIENTE SELECCIONADO (si hay) -->
    @if($cliente_seleccionado && $cliente_general != '1')
    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-user-check text-blue-500 mr-3 text-xl"></i>
                <div>
                    <span class="font-bold text-blue-800">Cliente Seleccionado:</span>
                    <span class="text-blue-700 ml-2">{{ $cliente_seleccionado->nombre }}</span>
                    <div class="text-sm text-blue-600">
                        <i class="fas fa-phone mr-1"></i>{{ $cliente_seleccionado->telefono }}
                        @if($cliente_seleccionado->email)
                        <span class="ml-3">
                            <i class="fas fa-envelope mr-1"></i>{{ $cliente_seleccionado->email }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <button 
                wire:click="limpiarSeleccionCliente"
                class="text-red-500 hover:text-red-700"
                title="Cambiar cliente"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif





    <!-- LISTA DE VENTAS PAUSADAS -->
    @if(count($ventas_pausadas_lista) > 0)
    <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <h3 class="font-bold text-gray-700 mb-2">Ventas Pausadas</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach($ventas_pausadas_lista as $venta)
            <div class="bg-white border border-gray-200 rounded p-3 hover:bg-gray-50 transition">
                <div class="font-medium text-gray-800">{{ $venta['cliente_nombre'] }}</div>
                <div class="text-sm text-gray-600">{{ $venta['telefono'] }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    {{ $venta['total_productos'] }} productos
                </div>
                <div class="text-xs text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    Pausada: {{ $venta['fecha_pausada'] }}
                </div>
                <div class="text-xs font-medium mt-1">
                    Total: Bs {{ number_format($venta['total_bolivares'], 2) }}
                </div>
                <button 
                    wire:click="reanudarVenta({{ $venta['id'] }})"
                    class="mt-2 w-full bg-green-500 text-white py-1 rounded text-sm hover:bg-green-600 transition flex items-center justify-center"
                >
                    <i class="fas fa-play mr-1"></i>
                    Reanudar Venta
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ALERTA DE CAJA CERRADA -->
    @if(!$caja_abierta)
    <div class="mb-4 p-4 bg-red-100 border border-red-400 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-3 text-xl"></i>
            <div>
                <h3 class="font-bold text-red-800">Caja Cerrada</h3>
                <p class="text-red-600">No puede realizar ventas hasta que abra la caja</p>
            </div>
        </div>
    </div>
    @endif

    <!-- HEADER PRINCIPAL -->
    <div class="bg-gray-200 flex items-center justify-center">
        <div class="w-full mx-auto py-2">
            <div class="flex flex-col sm:flex-col md:flex-row w-full md:space-x-2 space-y-2 md:space-y-0 mb-2 md:mb-4">
                
                <!-- VENTA NRO -->
                <div class="w-full sm:w-full md:w-1/4 min-h-[100px] sm:min-h-[110px] md:min-h-[120px] flex">
                    <div class="widget w-full p-3 sm:p-4 rounded-lg bg-white border-l-4 border-purple-400 flex items-center flex-1">
                        <div class="flex items-center w-full">
                            <div class="icon w-10 sm:w-12 md:w-14 lg:w-20 p-2 sm:p-3 md:p-3.5 bg-purple-400 text-white rounded-full mr-2 sm:mr-3">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="flex flex-col justify-center flex-1">
                                <div class="text-sm sm:text-base md:text-lg lg:text-xl text-gray-800 text-center font-bold">VENTA NRO.</div>
                                <div class="text-xs sm:text-sm md:text-lg text-gray-400 text-center">{{$venta_nro}}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CANTIDAD -->
                <div class="w-full sm:w-full md:w-1/4 min-h-[100px] sm:min-h-[110px] md:min-h-[120px] flex">
                    <div class="widget w-full p-3 sm:p-4 rounded-lg bg-white border-l-4 border-yellow-400 flex items-center flex-1">
                        <div class="flex items-center w-full">
                            <div class="icon w-10 sm:w-12 md:w-14 lg:w-20 p-2 sm:p-3 md:p-3.5 bg-yellow-400 text-white rounded-full mr-2 sm:mr-3">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                            <div class="flex flex-col justify-center flex-1">
                                <div class="text-sm sm:text-base md:text-xl text-gray-800 text-center font-bold mb-1 sm:mb-2">CANTIDAD</div>
                                @if($cant_producto)
                                <div class="text-xs sm:text-sm md:text-lg text-gray-400 text-center">{{$cant_producto}} productos</div>
                                @else
                                <div class="text-xs sm:text-sm md:text-lg text-gray-400 text-center">NO HAY VENTA ACTIVA</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTONES: PAUSAR Y FINALIZAR -->
                <div class="w-full sm:w-full md:w-1/4 min-h-[100px] sm:min-h-[110px] md:min-h-[120px] flex">
                    <div class="widget w-full rounded-lg bg-white border-l-4 border-purple-700 flex items-center justify-center flex-1">
                        @if($cant_producto)
                            <div class="flex gap-2 p-2 w-full">
                                <!-- Botón Pausar Venta -->
                                <button 
                                    wire:click="pausarVenta"
                                    wire:loading.attr="disabled"
                                    wire:target="pausarVenta"
                                    class="flex-1 bg-yellow-500 text-white px-4 py-3 rounded-lg hover:bg-yellow-600 transition flex items-center justify-center disabled:opacity-50"
                                    @if(!$caja_abierta) disabled @endif
                                    id="btn-pausar-venta"
                                >
                                    <span wire:loading.remove wire:target="pausarVenta">
                                        <i class="fas fa-pause mr-2"></i>
                                        Pausar
                                    </span>
                                    <span wire:loading wire:target="pausarVenta">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                        Procesando...
                                    </span>
                                </button>
                                
                                <!-- COMPONENTE PARA FINALIZAR VENTA -->
                                @livewire('punto-venta.punto-venta-finalizar', [
                                    'caja_abierta' => $caja_abierta
                                ], key('finalizar'))
                            </div>
                        @else
                            <div class="text-xs sm:text-sm md:text-lg text-gray-400 text-center">NO HAY VENTA ACTIVA</div>
                        @endif
                    </div>

                 </div>




                    <!-- TOTAL A PAGAR -->
                <div class="w-full sm:w-full md:w-1/4 min-h-[100px] sm:min-h-[110px] md:min-h-[120px] flex">
                    <div class="widget w-full p-2 sm:p-3 rounded-lg bg-white border-l-4 border-green-400 flex items-center flex-1">
                        <div class="flex flex-col justify-center w-full text-center">
                            @if($facturar_con_iva)
                                <div class="mt-3 border-t pt-3">
                                    <div class="text-sm text-gray-600">
                                        <div class="flex justify-between mb-1">
                                            <span>Subtotal sin {{ $nombre_impuesto }}:</span>
                                            <span>${{ number_format($subtotal_sin_iva, 2) }}</span>
                                        </div>
                                        
                                        @if($total_exento > 0)
                                        <div class="flex justify-between mb-1 text-green-600">
                                            <span>Exento de {{ $nombre_impuesto }}:</span>
                                            <span>${{ number_format($total_exento, 2) }}</span>
                                        </div>
                                        @endif
                                        
                                        @if($total_iva > 0)
                                        <div class="flex justify-between mb-1 text-red-600">
                                            <span>{{ $nombre_impuesto }} ({{ $porcentaje_iva }}%):</span>
                                            <span>${{ number_format($total_iva, 2) }}</span>
                                        </div>
                                        @endif
                                        
                                        <div class="flex justify-between font-semibold border-t pt-1 mt-1">
                                            <span>TOTAL A PAGAR:</span>
                                            <span>${{ number_format($total_global, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-sm sm:text-base md:text-xl text-gray-800 font-bold mb-1 sm:mb-2">TOTAL A PAGAR</div>
                                <div class="bg-yellow-100 text-yellow-800 shadow-sm text-sm sm:text-base md:text-lg font-medium px-1 sm:px-2 py-1 rounded-sm border border-yellow-300">
                                <p class="font-bold text-xs sm:text-sm md:text-2xl">Bs {{ number_format($this->total_bs, 2, ',', '.') }}</p>
                                    <span class="bg-green-200 text-green-800 text-md md:text-xl font-medium px-1 sm:px-2 rounded-sm border border-green-400">
                                        REF. {{ number_format($this->total_global, 2, ',', '.') }}
                                    </span>
                                </div>

                            @endif
                        </div>
                    </div>
                </div>
                
                
                
                
                
               
            

            

        

            </div>
        </div>
    </div>

    <!-- PANEL PRINCIPAL -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 p-2 lg:p-3 bg-gray-50 rounded-xl">
    
        <!-- PANEL DE BÚSQUEDA Y PRODUCTOS -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-4 lg:p-6">
            <!-- HEADER CON TOGGLE CLIENTE MODIFICADO -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-user text-blue-500 text-lg"></i>
                    <div>
                        <p class="text-xl font-bold text-gray-800">Cliente General</p>
                        @if($cliente_seleccionado && $cliente_general != '1')
                        <p class="text-sm text-green-600">
                            <i class="fas fa-check mr-1"></i>
                            {{ $cliente_seleccionado->nombre }}
                        </p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @if($cliente_seleccionado && $cliente_general != '1')
                    <button 
                        wire:click="limpiarSeleccionCliente"
                        class="text-sm text-red-500 hover:text-red-700"
                        title="Cambiar cliente"
                    >
                        <i class="fas fa-sync mr-1"></i>Cambiar
                    </button>
                    @endif
                    <div class="relative inline-block w-12 h-6">
                        <input 
                            type="checkbox"
                            wire:model="cliente_general"
                            id="cliente_general"
                            class="sr-only"
                        />
                        <label 
                            for="cliente_general" 
                            class="block w-12 h-6 rounded-full cursor-pointer transition-all duration-300 ease-in-out
                                {{ $cliente_general == '1' ? 'bg-blue-500' : 'bg-gray-300' }}"
                        >
                            <span class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-all duration-300 ease-in-out
                                        {{ $cliente_general == '1' ? 'transform translate-x-6' : '' }}"></span>
                        </label>
                    </div>
                </div>
            </div>

            <hr class="border-gray-200 mb-6">

            <!-- BARRA DE BÚSQUEDA -->
            <div class="relative mb-2">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input 
                    wire:model.debounce.300ms="search" 
                    wire:keydown.escape="open = false"   
                    type="text" 
                    class="w-full pl-10 pr-4 py-4 bg-gray-100 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg transition-all duration-300 placeholder-gray-500 shadow-sm
                           @if(!$caja_abierta) opacity-50 cursor-not-allowed @endif"
                    placeholder="Buscar producto por nombre o código..."
                    @if(!$caja_abierta) disabled @endif
                />
                @if(!$caja_abierta)
                <div class="absolute inset-0 bg-gray-200 bg-opacity-50 rounded-2xl flex items-center justify-center">
                    <span class="text-gray-600 font-medium">Caja cerrada</span>
                </div>
                @endif
            </div>

            <!-- LISTA DE PRODUCTOS -->
            <div class="{{ $open ? '' : 'hidden' }}" x-show="$wire.open" @click.away="$wire.open = false">
                <div class="mt-2 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl shadow-lg border border-amber-200">
                    <div class="p-4 max-h-96 overflow-y-auto">
                        @forelse ($registros as $registro)
                            <div class="flex items-center justify-between mb-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl border border-amber-100 hover:bg-white hover:shadow-md transition-all duration-300 group">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-800 text-sm truncate group-hover:text-blue-600">{{$registro->nombre}}</h3>
                                    <div class="flex items-center gap-3 mt-2">
                                        <span class="font-bold text-gray-900">Bs {{ number_format($this->precio_bolivares($registro->precio_venta), 2, ',', '.') }}</span>
                                        <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-3 py-1 rounded-full border border-yellow-300">
                                            $ {{ number_format($registro->precio_venta, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- COMPONENTE DE CANTIDADES -->
                                <div class="ml-3">
                                    @livewire('punto-venta.punto-venta-cantidades', [
                                        'registro' => $registro,
                                        'caja_abierta' => $caja_abierta
                                    ], key($registro->id))
                                </div>
                            </div>
                        @empty
                            @if($search)
                                <div class="text-center py-6 bg-white/50 rounded-xl">
                                    <i class="fas fa-search text-gray-400 text-2xl mb-2"></i>
                                    <p class="text-gray-600 font-semibold">No se encontraron productos</p>
                                </div>
                            @endif
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- CARRITO DE COMPRAS -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-4 lg:p-6">
            @if ($registros_carro && count($registros_carro) > 0)
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-shopping-cart text-green-500 text-lg"></i>
                        <h2 class="text-xl font-bold text-gray-800">Carrito de Compras</h2>
                    </div>
                    <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                        {{ count($registros_carro) }} items
                    </span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm">
                    <table class="w-full text-sm text-gray-700">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 text-gray-700 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-sm">Producto</th>
                                <th class="px-4 py-3 text-center font-semibold text-sm">Cantidad</th>
                                <th class="px-4 py-3 text-center font-semibold text-sm">Subtotal</th>
                                <th class="px-4 py-3 text-center font-semibold text-sm">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($registros_carro as $registro_c)
                                <tr class="bg-white hover:bg-blue-50 transition-colors duration-200 group">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-box text-white text-xs"></i>
                                            </div>
                                            <span class="font-medium text-gray-800 text-sm">{{$registro_c->producto->nombre}}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                            {{$registro_c->cantidad}}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col items-center space-y-1">
                                            <span class="font-bold text-gray-900">Bs {{ number_format($this->subtotal_bol($registro_c->producto_id,$registro_c->cantidad), 2, ',', '.') }}</span>
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full border border-green-300">
                                                REF. ${{ number_format($this->subtotal_dol($registro_c->producto_id,$registro_c->cantidad), 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button
                                            wire:click="delete('{{$registro_c->id}}')"
                                            wire:loading.class="opacity-50"
                                            class="w-10 h-10 bg-red-500 hover:bg-red-600 text-white rounded-xl flex items-center justify-center transition-all duration-300 transform hover:scale-110 group-hover:shadow-lg"
                                            title="Eliminar del carrito"
                                        >
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @else
                <!-- ESTADO VACÍO DEL CARRITO -->
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shopping-cart text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-600 mb-2">Carrito Vacío</h3>
                    <p class="text-gray-500 text-sm">Agrega productos desde el panel de búsqueda</p>
                    @if(!$caja_abierta)
                    <p class="text-red-500 text-sm mt-2">⚠️ La caja está cerrada</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Script para las notificaciones
    document.addEventListener('livewire:load', function() {
        window.livewire.on('notify', (data) => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: {
                    message: data.message,
                    type: data.type
                }
            }));
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificar que el botón existe
    const btnPausar = document.getElementById('btn-pausar-venta');
    if (btnPausar) {
        console.log('✅ Botón pausar encontrado');
        
        btnPausar.addEventListener('click', function(e) {
            console.log('🟡 Click en botón pausar');
            console.log('Livewire componente:', @this);
        });
    } else {
        console.error('❌ Botón pausar NO encontrado');
    }

    // Verificar variables de Livewire
    console.log('Caja abierta:', @this.caja_abierta);
    console.log('Cantidad productos:', @this.cant_producto);
    console.log('Cliente general:', @this.cliente_general);
});
</script>

<script>
// Capturar eventos de Livewire
document.addEventListener('livewire:load', function() {
    // Evento cuando se abre el modal
    Livewire.on('modal-pausa-abierto', () => {
        console.log('🟡 Modal de pausa abierto');
    });
    
    // Evento para logs en consola
    Livewire.on('console-log', (data) => {
        console.log('Livewire Log:', data.message);
    });
    
    // Verificar que Livewire está cargado
    console.log('Livewire cargado para PuntoVentaCreate');
    
    // Escuchar todos los eventos de Livewire
    Livewire.hook('message.processed', (message, component) => {
        console.log('Evento Livewire:', message.updateQueue[0]?.payload?.event);
    });
});
</script>

<script>
// Escuchar evento de confirmación de reemplazo
Livewire.on('confirmar-reemplazo', (data) => {
    if (confirm(`Ya tienes una venta activa.\n¿Deseas reemplazarla por la venta de ${data.clienteNombre}?`)) {
        Livewire.emit('confirmarReemplazarCarrito', data.ventaId);
    }
});

// También modifica el evento de reanudar en el botón
document.addEventListener('DOMContentLoaded', function() {
    // Delegación de eventos para botones de reanudar
    document.addEventListener('click', function(e) {
        if (e.target.closest('button') && e.target.closest('button').hasAttribute('wire:click')) {
            const wireClick = e.target.closest('button').getAttribute('wire:click');
            if (wireClick && wireClick.startsWith('reanudarVenta')) {
                // El manejo ahora está en Livewire con confirmación
                console.log('Reanudar venta clickeado');
            }
        }
    });
});
</script>