<div class="font-Arima">
    <style>
        .icon::after{
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
        
        .update-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .update-btn:active {
            transform: translateY(0);
        }
        
        .update-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .update-btn:hover::after {
            left: 100%;
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(102, 126, 234, 0); }
            100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0); }
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
        
        /* Estilos para opciones de comprobante */
        .comprobante-option {
            transition: all 0.2s ease;
        }
        
        .comprobante-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .comprobante-option.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
    </style>

    <!-- SCRIPT PARA IMPRESIÓN AUTOMÁTICA -->
    <script>
        // Función para imprimir automáticamente
        function imprimirComprobanteAutomatico(url) {
            // Crear un iframe oculto
            const iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = 'none';
            iframe.src = url;
            
            document.body.appendChild(iframe);
            
            // Esperar a que cargue e imprimir
            iframe.onload = function() {
                try {
                    // Pequeña espera para asegurar carga
                    setTimeout(() => {
                        iframe.contentWindow.print();
                        
                        // Eliminar el iframe después de imprimir
                        setTimeout(() => {
                            document.body.removeChild(iframe);
                        }, 1000);
                    }, 500);
                } catch (e) {
                    console.error('Error al imprimir:', e);
                    // Si falla, abrir en nueva pestaña
                    window.open(url, '_blank');
                }
            };
        }
        
        // Escuchar evento de Livewire para imprimir
        document.addEventListener('livewire:load', function() {
            Livewire.on('imprimir-comprobante', (data) => {
                console.log('Imprimiendo comprobante:', data);
                
                // Preguntar al usuario si quiere imprimir
                if (confirm('¿Desea imprimir el ' + data.tipo + '?')) {
                    imprimirComprobanteAutomatico(data.url);
                } else {
                    // Si no quiere imprimir, solo descargar
                    window.open(data.url, '_blank');
                }
            });
        });
    </script>

    <button 
        type="button" 
        wire:click="openModal" 
        wire:loading.attr="disabled" 
        class="cursor-pointer update-btn w-full p-4 text-white font-bold text-lg rounded-xl flex items-center justify-center">
        
        <svg wire:loading.remove class="w-4 sm:w-5 h-4 sm:h-5 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        
        <span wire:loading.remove class="group-hover:text-gray-800 transition-colors">
            PROCESAR VENTA
        </span>
        
        <span wire:loading>Procesando...</span>
    </button>

    <x-dialog-modal wire:model="open" maxWidth="xl">
        <x-slot name="title">
            <div class="flex justify-between">
                <p></p>
                <button type="button" wire:click="close" wire:loading.attr="disabled" class="cursor-pointer py-2.5 px-3 me-2 mb-2 text-sm font-bold text-white focus:outline-none bg-black rounded-full border border-gray-200 hover:bg-gray-100 hover:text-gray-200 focus:z-10 focus:ring-4 focus:ring-gray-100">
                    X
                </button>
            </div>
        </x-slot>

        <x-slot name="content">
            <div>
                <!-- MÉTODO DE PAGO -->
                <div class="relative mb-4">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-600" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                            viewBox="0 0 485.211 485.21" style="enable-background:new 0 0 485.211 485.21;"
                            xml:space="preserve">
                            <!-- SVG de pago (mantener igual) -->
                        </svg>
                    </div>
                    <select wire:model="metodo_pago" class="w-full pl-10 pr-10 py-3 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none cursor-pointer transition-all duration-200">
                        <option value="" selected>Selecciona el método de pago</option>
                        <option value="debito">Débito</option>
                        <option value="pago_movil">Pago móvil</option>
                        <option value="bio">Biopago</option>
                        <option value="dol_efec">Dólares en efectivo</option>
                        <option value="bs_efec">Bolívares en efectivo</option>
                        <option value="usdt">USDT</option>
                    </select>

                    @error('metodo_pago')
                        <p class="text-red-500 text-sm mt-1">El método de pago es requerido </p>
                    @enderror
                </div>

                <!-- OPCIONES DE COMPROBANTE -->
                <div class="mb-6">
                    <p class="text-sm font-semibold text-gray-700 mb-2">Tipo de comprobante:</p>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <input type="radio" id="ticket" wire:model="tipo_comprobante" value="ticket" class="sr-only">
                            <label for="ticket" class="comprobante-option flex flex-col items-center justify-center p-3 border-2 rounded-lg cursor-pointer {{ $tipo_comprobante == 'ticket' ? 'selected border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                                <i class="fas fa-receipt text-xl {{ $tipo_comprobante == 'ticket' ? 'text-blue-500' : 'text-gray-400' }} mb-2"></i>
                                <span class="text-sm font-medium">Ticket</span>
                            </label>
                        </div>
                        
                        <div>
                            <input type="radio" id="factura" wire:model="tipo_comprobante" value="factura" class="sr-only">
                            <label for="factura" class="comprobante-option flex flex-col items-center justify-center p-3 border-2 rounded-lg cursor-pointer {{ $tipo_comprobante == 'factura' ? 'selected border-green-500 bg-green-50' : 'border-gray-300' }}">
                                <i class="fas fa-file-invoice text-xl {{ $tipo_comprobante == 'factura' ? 'text-green-500' : 'text-gray-400' }} mb-2"></i>
                                <span class="text-sm font-medium">Factura</span>
                            </label>
                        </div>
                        
                        <div>
                            <input type="radio" id="ninguno" wire:model="tipo_comprobante" value="ninguno" class="sr-only">
                            <label for="ninguno" class="comprobante-option flex flex-col items-center justify-center p-3 border-2 rounded-lg cursor-pointer {{ $tipo_comprobante == 'ninguno' ? 'selected border-gray-500 bg-gray-100' : 'border-gray-300' }}">
                                <i class="fas fa-ban text-xl {{ $tipo_comprobante == 'ninguno' ? 'text-gray-500' : 'text-gray-400' }} mb-2"></i>
                                <span class="text-sm font-medium">Ninguno</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN PAGO EN BOLÍVARES -->
                {{-- @if($metodo_pago == "bs_efec")
                <hr class="my-4 text-gray-200">
                
                <div class="flex justify-start items-center mb-4">
                    <div class="py-4 px-4 rounded-md">
                        <div class="flex items-center">
                            <p class="text-xs text-gray-800 font-semibold pb-3 mr-3">Recibido el monto exacto</p>
                            <div class="w-10 h-4 cursor-pointer rounded-full relative shadow-sm">
                                <input aria-label="subscribe"
                                    type="checkbox"
                                    value='1'
                                    name="monto_cancelado"
                                    wire:model="monto_cancelado"
                                    id="monto_cancelado"
                                    class="focus:ring-2 focus:ring-offset-2 focus:ring-indigo-700 focus:bg-indigo-600 focus:outline-none checkbox w-4 h-4 rounded-full bg-white border-2 border-gray-700 absolute shadow-sm appearance-none cursor-pointer" />
                                <label for="monto_cancelado" class="toggle-label bg-gray-200 block w-10 h-4 overflow-hidden rounded-full border-2 border-blue-700 bg-gray-300 cursor-pointer"></label>
                            </div>
                        </div>
                    </div>

                    @if($monto_cancelado == 0)
                    <div class="ml-2">
                        <div class="flex">
                            <input 
                                type="number" 
                                id="montocbs"
                                wire:model.live="montocbs"
                                step="0.01"
                                min="0"
                                class="w-full px-4 mr-1 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white placeholder-gray-400"
                                placeholder="Monto recibido"
                            >
                            <p class="text-xl font-medium text-gray-700 ml-1 mt-2">Bs</p>
                        </div>
                        @error('montocbs')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif
                </div>

                @if($monto_cancelado == 0)
                <div class="flex flex-col justify-center items-center mb-4">
                    <div class="mt-1 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="bg-green-50 p-4 text-green-800 text-xs font-medium rounded-2xl border-2 border-green-400">
                            <div class="p-4 flex w-auto flex-col justify-center text-center">
                                <p class="text-sm font-bold text-green-800">CAMBIO</p>
                                <h4 class="text-xl text-center font-bold text-green-800">{{ $cambio ?? '0' }} Bs</h4>
                            </div>
                        </div>
                        
                        <div class="bg-red-50 p-4 text-red-800 text-xs font-medium rounded-2xl border-2 border-red-400">
                            <div class="p-4 flex w-auto flex-col justify-center text-center">
                                <p class="text-sm font-bold text-red-800">DEUDA CLIENTE</p>
                                <h4 class="text-xl text-center font-bold text-red-800">{{ $deuda ?? '0' }} Bs</h4>
                            </div>
                        </div>
                    </div> 
                </div>
                @endif
                @endif

                <!-- SECCIÓN PAGO EN DÓLARES -->
                @if($metodo_pago == "dol_efec")
                <hr class="my-4 text-gray-200">
                
                <div class="flex justify-start items-center mb-4">
                    <div class="py-4 px-4 rounded-md">
                        <div class="flex items-center">
                            <p class="text-xs text-gray-800 font-semibold pb-3 mr-3">Recibido el monto exacto</p>
                            <div class="w-10 h-4 cursor-pointer rounded-full relative shadow-sm">
                                <input aria-label="subscribe"
                                    type="checkbox"
                                    value='1'
                                    name="monto_cancelado"
                                    wire:model="monto_cancelado"
                                    id="monto_cancelado"
                                    class="focus:ring-2 focus:ring-offset-2 focus:ring-indigo-700 focus:bg-indigo-600 focus:outline-none checkbox w-4 h-4 rounded-full bg-white border-2 border-gray-700 absolute shadow-sm appearance-none cursor-pointer" />
                                <label for="monto_cancelado" class="toggle-label bg-gray-200 block w-10 h-4 overflow-hidden rounded-full border-2 border-blue-700 bg-gray-300 cursor-pointer"></label>
                            </div>
                        </div>
                    </div>

                    @if($monto_cancelado == 0)
                    <div class="ml-2">
                        <div class="flex">
                            <input 
                                type="number" 
                                id="montocdol"
                                wire:model.live="montocdol"
                                step="0.01"
                                min="0"
                                class="w-full px-4 mr-1 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white placeholder-gray-400"
                                placeholder="Monto recibido"
                            >
                            <p class="text-xl font-medium text-gray-700 ml-1 mt-2">$</p>
                        </div>
                        @error('montocdol')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif
                </div>

                @if($monto_cancelado == 0)
                <div class="flex flex-col justify-center items-center mb-4">
                    <div class="mt-1 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="bg-green-50 p-4 text-green-800 text-xs font-medium rounded-2xl border-2 border-green-400">
                            <div class="p-4 flex w-auto flex-col justify-center text-center">
                                <p class="text-sm font-bold text-green-800">CAMBIO</p>
                                <h4 class="text-xl text-center font-bold text-green-800">{{ $cambio ?? '0' }} $</h4>
                            </div>
                        </div>
                        
                        <div class="bg-red-50 p-4 text-red-800 text-xs font-medium rounded-2xl border-2 border-red-400">
                            <div class="p-4 flex w-auto flex-col justify-center text-center">
                                <p class="text-sm font-bold text-red-800">DEUDA CLIENTE</p>
                                <h4 class="text-xl text-center font-bold text-red-800">{{ $deuda ?? '0' }} $</h4>
                            </div>
                        </div>
                    </div> 
                </div>
                @endif
                @endif --}}

                <!-- SECCIÓN REGISTRO DE DEUDA (si hay) -->
                {{-- @if($registrar_deuda && $deuda > 0)
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                        <h3 class="font-bold text-yellow-800 text-sm">Registro de Deuda</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-yellow-700 mb-1">
                                Fecha límite de pago *
                            </label>
                            <input 
                                type="date" 
                                wire:model="fecha_limite_deuda"
                                min="{{ date('Y-m-d') }}"
                                class="w-full px-3 py-2 border border-yellow-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm"
                            >
                            @error('fecha_limite_deuda')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-yellow-700 mb-1">
                                Comentario (opcional)
                            </label>
                            <textarea 
                                wire:model="comentario_deuda"
                                rows="2"
                                class="w-full px-3 py-2 border border-yellow-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm"
                                placeholder="Ej: Acordó pagar la próxima semana..."
                            ></textarea>
                        </div>
                    </div>
                </div>
                @endif

                <!-- SECCIÓN: PREGUNTAR SI HAY DEUDA -->
                @if($deuda > 0 && !$monto_cancelado)
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <i class="fas fa-question-circle text-blue-500 mr-2"></i>
                            <h3 class="font-bold text-blue-800 text-sm">¿El cliente quedó debiendo?</h3>
                        </div>
                        <div class="w-12 h-6 cursor-pointer rounded-full relative">
                            <input 
                                type="checkbox"
                                wire:model="hay_deuda"
                                id="hay_deuda"
                                class="sr-only"
                            />
                            <label 
                                for="hay_deuda" 
                                class="block w-12 h-6 rounded-full cursor-pointer transition-all duration-300 ease-in-out
                                    {{ $hay_deuda ? 'bg-blue-500' : 'bg-gray-300' }}"
                            >
                                <span class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-all duration-300 ease-in-out
                                            {{ $hay_deuda ? 'transform translate-x-6' : '' }}"></span>
                            </label>
                        </div>
                    </div>
                    
                    @if($hay_deuda)
                    <div class="text-blue-700 text-sm mb-3">
                        <p class="font-medium">Deuda pendiente:</p>
                        <p class="font-bold">
                            @if($metodo_pago == 'dol_efec')
                                ${{ number_format($monto_deuda_dol, 2) }} (Bs {{ number_format($monto_deuda_bs, 2) }})
                            @elseif($metodo_pago == 'bs_efec')
                                Bs {{ number_format($monto_deuda_bs, 2) }} (${{ number_format($monto_deuda_dol, 2) }})
                            @endif
                        </p>
                    </div>
                    
                    <!-- FORMULARIO PARA DETALLES DE LA DEUDA -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-blue-700 mb-1">
                                Fecha límite de pago *
                            </label>
                            <input 
                                type="date" 
                                wire:model="fecha_limite_deuda"
                                min="{{ date('Y-m-d') }}"
                                class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                            @error('fecha_limite_deuda')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-blue-700 mb-1">
                                Comentario sobre la deuda (opcional)
                            </label>
                            <textarea 
                                wire:model="comentario_deuda"
                                rows="2"
                                class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                placeholder="Ej: Pagará la próxima semana, dejó un recibo, etc..."
                            ></textarea>
                        </div>
                    </div>
                    @elseif($deuda > 0)
                    <div class="text-gray-600 text-sm">
                        <i class="fas fa-info-circle mr-1"></i>
                        El cliente pagó completo. No hay deuda pendiente.
                    </div>
                    @endif
                </div>
                @endif

                <!-- COMENTARIO DE VENTA -->
                <div class="relative mt-3">
                    <textarea 
                        rows="3"
                        wire:model="comentario_venta"
                        class="w-full px-4 py-4 text-gray-500 bg-white border border-gray-300 rounded-2xl shadow-inner focus:outline-none focus:ring-4 focus:ring-amber-500/30 focus:border-amber-400 resize-none transition-all duration-300 hover:shadow-lg font-medium text-sm"
                        placeholder="💡 Comentario sobre la venta (opcional)..."
                    ></textarea>
                </div> --}}


            @if($metodo_pago)
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                    <h3 class="font-bold text-blue-800 text-sm mb-3 flex items-center">
                        <i class="fas fa-credit-card mr-2"></i>
                        Información de Pago
                    </h3>
                    
                    <!-- PARA EFECTIVO: MONTO RECIBIDO -->
                    @if(in_array($metodo_pago, ['bs_efec', 'dol_efec']))
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-blue-700 mb-2">
                            Monto recibido en efectivo:
                        </label>
                        
                        @if($metodo_pago == 'dol_efec')
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">$</span>
                            </div>
                            <input 
                                type="number" 
                                wire:model.live="montocdol"
                                step="0.01"
                                min="0"
                                class="w-full pl-10 pr-4 py-3 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                placeholder="Ej: 10.00"
                            >
                        </div>
                        @elseif($metodo_pago == 'bs_efec')
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">Bs</span>
                            </div>
                            <input 
                                type="number" 
                                wire:model.live="montocbs"
                                step="0.01"
                                min="0"
                                class="w-full pl-10 pr-4 py-3 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                placeholder="Ej: 100.00"
                            >
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    <!-- PARA MÉTODOS ELECTRÓNICOS: PREGUNTAR SI PAGÓ COMPLETO -->
                    @if(in_array($metodo_pago, ['debito', 'pago_movil', 'usdt','biopago']))
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <span class="text-sm font-medium text-gray-700">¿El cliente pagó completo?</span>
                            </div>
                            <div class="w-10 h-4 cursor-pointer rounded-full relative shadow-sm">
                                <input 
                                    type="checkbox"
                                    value='1'
                                    wire:model="monto_cancelado"
                                    id="pago_completo_electronico"
                                    class="focus:ring-2 focus:ring-offset-2 focus:ring-indigo-700 focus:bg-indigo-600 focus:outline-none checkbox w-4 h-4 rounded-full bg-white border-2 border-gray-700 absolute shadow-sm appearance-none cursor-pointer" 
                                />
                                <label for="pago_completo_electronico" class="toggle-label bg-gray-200 block w-10 h-4 overflow-hidden rounded-full border-2 border-blue-700 bg-gray-300 cursor-pointer"></label>
                            </div>
                        </div>
                        
                        <!-- SI NO PAGÓ COMPLETO, PREGUNTAR CUÁNTO PAGÓ -->
                        @if($monto_cancelado == 0)
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-blue-700 mb-2">
                                ¿Cuánto pagó el cliente?
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">$</span>
                                </div>
                                <input 
                                    type="number" 
                                    wire:model.live="montocdol"
                                    step="0.01"
                                    min="0"
                                    max="{{ $total_dol }}"
                                    class="w-full pl-10 pr-4 py-3 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                    placeholder="Monto pagado en $"
                                >
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Total: <span class="font-bold">${{ number_format($total_dol, 2) }}</span>
                                (Bs {{ number_format($total_bs, 2) }})
                            </p>
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    <!-- RESUMEN DE PAGO (PARA TODOS) -->
                    <div class="mt-4 space-y-3">
                        <!-- TOTAL -->
                        <div class="p-3 bg-gray-100 border border-gray-200 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Total a pagar:</span>
                                <div class="text-right">
                                    <span class="font-bold text-gray-900">
                                        ${{ number_format($total_dol, 2) }} 
                                        <span class="text-gray-600">(Bs {{ number_format($total_bs, 2) }})</span>
                                    </span>
                                    
                                    @if($facturar_con_iva)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Incluye {{ $nombre_impuesto }} ({{ $porcentaje_iva }}%)
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- DESGLOSE DE IMPUESTOS (solo si hay IVA) -->
                            @if($facturar_con_iva && $total_iva > 0)
                            <div class="mt-2 pt-2 border-t border-gray-300">
                                <div class="text-xs text-gray-600 space-y-1">
                                    <div class="flex justify-between">
                                        <span>Subtotal sin {{ $nombre_impuesto }}:</span>
                                        <span>${{ number_format($subtotal_sin_iva, 2) }}</span>
                                    </div>
                                    
                                    @if($total_exento > 0)
                                    <div class="flex justify-between text-green-600">
                                        <span>Exento de {{ $nombre_impuesto }}:</span>
                                        <span>${{ number_format($total_exento, 2) }}</span>
                                    </div>
                                    @endif
                                    
                                    <div class="flex justify-between text-blue-600">
                                        <span>{{ $nombre_impuesto }} ({{ $porcentaje_iva }}%):</span>
                                        <span>${{ number_format($total_iva, 2) }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between font-bold pt-1 border-t border-gray-300">
                                        <span>TOTAL:</span>
                                        <span>${{ number_format($total_dol, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <!-- PAGO COMPLETO (SI APLICA) -->
                        @if($monto_cancelado == 1 || ($deuda == 0 && ($montocdol || $montocbs)))
                        <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <span class="text-sm font-medium text-green-700">
                                    Pago completo recibido
                                </span>
                            </div>
                        </div>
                        @endif
                        
                        <!-- DEUDA (SI HAY) -->
                        @if($deuda > 0 && $monto_cancelado == 0)
                        <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center">
                                    <i class="fas fa-hand-holding-usd text-red-500 mr-2"></i>
                                    <span class="text-sm font-medium text-red-700">Deuda pendiente</span>
                                </div>
                                <span class="font-bold text-red-700">
                                    ${{ number_format($deuda, 2) }}
                                    <span class="text-red-600">(Bs {{ number_format($deuda * $tasa_actual, 2) }})</span>
                                </span>
                            </div>
                            
                            <!-- PREGUNTAR SI REGISTRAR DEUDA -->
                            <div class="mt-3">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm text-gray-700">¿Registrar esta deuda en el sistema?</span>
                                    <label class="toggle-switch">
                                        <input 
                                            type="checkbox" 
                                            wire:model="hay_deuda"
                                            id="registrar_deuda_{{ $metodo_pago }}"
                                        >
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                
                                <!-- DETALLES DE DEUDA SI SE MARCA -->
                                @if($hay_deuda)
                                <div class="mt-3 space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-red-700 mb-1">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            Fecha límite de pago *
                                        </label>
                                        <input 
                                            type="date" 
                                            wire:model="fecha_limite_deuda"
                                            min="{{ date('Y-m-d') }}"
                                            class="w-full px-3 py-2 text-sm border border-red-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        >
                                        @error('fecha_limite_deuda')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-red-700 mb-1">
                                            <i class="fas fa-comment mr-1"></i>
                                            Comentario (opcional)
                                        </label>
                                        <textarea 
                                            wire:model="comentario_deuda"
                                            rows="2"
                                            class="w-full px-3 py-2 text-sm border border-red-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                            placeholder="Detalles del acuerdo de pago..."
                                        ></textarea>
                                    </div>
                                    
                                    <div class="bg-yellow-50 p-2 rounded border border-yellow-200">
                                        <p class="text-xs text-yellow-700">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Esta deuda quedará registrada y aparecerá en el historial del cliente.
                                        </p>
                                    </div>
                                </div>
                                @else
                                <div class="bg-gray-50 p-2 rounded border border-gray-200">
                                    <p class="text-xs text-gray-600">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        La deuda NO se registrará formalmente en el sistema.
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- 4. COMENTARIO DE VENTA -->
                <div class="relative mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sticky-note mr-1"></i>
                        Comentario sobre la venta (opcional)
                    </label>
                    <textarea 
                        rows="3"
                        wire:model="comentario_venta"
                        class="w-full px-4 py-3 text-gray-700 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none transition-all duration-300 text-sm"
                        placeholder="Observaciones, descuentos especiales, detalles del cliente..."
                    ></textarea>
                </div>
            </div>


            <!-- RESUMEN FINAL DE LA TRANSACCIÓN -->
        @if($metodo_pago && ($montocdol || $montocbs || $monto_cancelado == 1))
        <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center">
                <i class="fas fa-clipboard-check mr-2"></i>
                Resumen final
            </h3>
            
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total venta:</span>
                    <div class="text-right">
                        <span class="font-bold">
                            ${{ number_format($total_dol, 2) }} 
                            <span class="text-gray-500">(Bs {{ number_format($total_bs, 2) }})</span>
                        </span>
                        
                        @if($facturar_con_iva)
                            <div class="text-xs text-gray-500">
                                <span class="inline-block px-1 bg-blue-100 text-blue-800 rounded">
                                    {{ $nombre_impuesto }} incluido
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Monto pagado:</span>
                    <span class="font-medium text-green-600">
                        @if($metodo_pago == 'bs_efec')
                            Bs {{ number_format($montocbs ?? 0, 2) }}
                        @else
                            ${{ number_format($montocdol ?? 0, 2) }}
                        @endif
                    </span>
                </div>
                
                @if($deuda > 0)
                <div class="flex justify-between">
                    <span class="text-gray-600">Deuda pendiente:</span>
                    <span class="font-bold {{ $hay_deuda ? 'text-red-600' : 'text-orange-600' }}">
                        @if(in_array($metodo_pago, ['debito', 'pago_movil', 'usdt', 'dol_efec','biopago']))
                            ${{ number_format($deuda, 2) }}
                        @else
                            Bs {{ number_format($deuda, 2) }}
                        @endif
                        
                        @if($hay_deuda)
                        <span class="text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded ml-1">
                            <i class="fas fa-exclamation-triangle mr-1"></i>REGISTRADA
                        </span>
                        @else
                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-0.5 rounded ml-1">
                            NO REGISTRADA
                        </span>
                        @endif
                    </span>
                </div>
                
                @if($hay_deuda && $fecha_limite_deuda)
                <div class="flex justify-between">
                    <span class="text-gray-600">Fecha límite:</span>
                    <span class="font-medium">
                        {{ \Carbon\Carbon::parse($fecha_limite_deuda)->format('d/m/Y') }}
                    </span>
                </div>
                @endif
                @endif
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Método pago:</span>
                    <span class="font-medium">
                        {{ ucfirst(str_replace('_', ' ', $metodo_pago)) }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Comprobante:</span>
                    <span class="font-medium">
                        @if($tipo_comprobante == 'ticket')
                            <span class="text-blue-600">Ticket</span>
                        @elseif($tipo_comprobante == 'factura')
                            <span class="text-green-600">Factura</span>
                        @else
                            <span class="text-gray-600">Ninguno</span>
                        @endif
                    </span>
                </div>
            </div>
            
            @if($hay_deuda && $deuda > 0)
            <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded">
                <p class="text-xs text-red-700 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span>
                        <strong>Importante:</strong> Esta deuda quedará registrada en el sistema 
                        y aparecerá en el historial del cliente hasta que sea pagada.
                    </span>
                </p>
            </div>
            @endif
        </div>
        @endif
          
        </x-slot>

        <x-slot name="footer">
            <button 
                type="button" 
                wire:click="save"
                wire:loading.attr="disabled"
                class="cursor-pointer update-btn w-full p-4 text-white font-bold text-lg rounded-xl flex items-center justify-center"
                @if($hay_deuda && empty($fecha_limite_deuda)) disabled @endif>
                
                @if($hay_deuda && empty($fecha_limite_deuda))
                <span class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    COMPLETAR FECHA LÍMITE
                </span>
                @else
                <span wire:loading.remove>FINALIZAR VENTA</span>
                <span wire:loading>
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Procesando...
                </span>
                @endif
            </button>
        </x-slot>
    </x-dialog-modal>
</div>