<div>
    <!-- Estado de Caja -->
    @if($caja_activa)
        <div class="bg-green-100 border border-green-400 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-green-800">✅ Caja Abierta</h3>
                    <p class="text-sm text-green-600">
                        Saldo: Bs {{ number_format($caja_activa->saldo_bolivares, 2) }} | 
                        $ {{ number_format($caja_activa->saldo_dolares, 2) }}
                    </p>
                    <p class="text-xs text-green-500 mt-1">
                        ID: {{ $caja_activa->id }} | 
                        Estado: {{ $caja_activa->status }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <button 
                        wire:click="verResumen"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm"
                    >
                        Ver Resumen
                    </button>
                    <button 
                        wire:click="cerrarCaja"
                        wire:confirm="¿Está seguro de cerrar la caja?"
                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-sm"
                    >
                        Cerrar Caja
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="bg-yellow-100 border border-yellow-400 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-yellow-800">⏸️ Caja Cerrada</h3>
                    <p class="text-sm text-yellow-600">Debe abrir la caja para realizar ventas</p>
                </div>
                <button 
                    wire:click="$set('mostrar_modal_apertura', true)"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
                >
                    Abrir Caja
                </button>
            </div>
        </div>
    @endif

    <!-- Modal Apertura -->
    @if($mostrar_modal_apertura)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Apertura de Caja</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Monto Inicial Bs</label>
                    <input 
                        type="number" 
                        step="0.01"
                        min="0"
                        wire:model="monto_inicial_bs"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                    >
                    @error('monto_inicial_bs') 
                        <span class="text-red-500 text-xs">{{ $message }}</span> 
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Monto Inicial $</label>
                    <input 
                        type="number" 
                        step="0.01"
                        min="0"
                        wire:model="monto_inicial_dolares"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                    >
                    @error('monto_inicial_dolares') 
                        <span class="text-red-500 text-xs">{{ $message }}</span> 
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <button 
                    wire:click="$set('mostrar_modal_apertura', false)"
                    class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 text-gray-700"
                >
                    Cancelar
                </button>
                <button 
                    wire:click="abrirCaja"
                    wire:loading.attr="disabled"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
                >
                    <span wire:loading.remove>Abrir Caja</span>
                    <span wire:loading>Abriendo...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Resumen -->
    @if($mostrar_resumen && $resumen)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Resumen de Caja</h3>
            
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Monto Inicial Bs:</span>
                    <span class="font-bold">Bs {{ number_format($resumen['monto_inicial_bs'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Monto Inicial $:</span>
                    <span class="font-bold">$ {{ number_format($resumen['monto_inicial_dolares'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Ventas Bs:</span>
                    <span class="font-bold">Bs {{ number_format($resumen['ventas_bs'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Ventas $:</span>
                    <span class="font-bold">$ {{ number_format($resumen['ventas_dolares'], 2) }}</span>
                </div>
                <hr>
                <div class="flex justify-between text-lg font-bold">
                    <span>Saldo Actual Bs:</span>
                    <span>Bs {{ number_format($resumen['saldo_actual_bs'], 2) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold">
                    <span>Saldo Actual $:</span>
                    <span>$ {{ number_format($resumen['saldo_actual_dolares'], 2) }}</span>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button 
                    wire:click="$set('mostrar_resumen', false)"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Debug info (solo para desarrollo) -->
    @if(env('APP_DEBUG'))
    <div class="mt-4 p-3 bg-gray-100 rounded text-xs">
        <strong>Debug Info:</strong><br>
        Caja Activa: {{ $caja_activa ? 'Sí (ID: ' . $caja_activa->id . ')' : 'No' }}<br>
        Monto BS: {{ $monto_inicial_bs }}<br>
        Monto USD: {{ $monto_inicial_dolares }}
    </div>
    @endif
</div>