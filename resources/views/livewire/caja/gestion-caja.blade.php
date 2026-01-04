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
                        wire:click="confirmarCerrarCaja"
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

    <!-- Modal Confirmación Cierre de Caja -->
   <!-- Modal Confirmación Cierre de Caja -->
    @if($mostrar_modal_cierre)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4 text-red-600">⚠️ Confirmar Cierre de Caja</h3>
            
            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-sm text-yellow-800">
                    <strong>¿Está seguro que desea cerrar la caja?</strong>
                </p>
                <p class="text-xs text-yellow-600 mt-1">
                    Al cerrar la caja no podrá realizar más ventas hasta abrir una nueva caja.
                </p>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Observaciones (opcional)</label>
                    <textarea 
                        wire:model="observaciones_cierre"
                        rows="3"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ej: Cierre normal, problema con terminal, etc."
                    ></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <button 
                    wire:click="$set('mostrar_modal_cierre', false)"
                    class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 text-gray-700"
                >
                    Cancelar
                </button>
                <button 
                    wire:click="cerrarCaja"
                    wire:loading.attr="disabled"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 disabled:opacity-50"
                >
                    <span wire:loading.remove>Sí, Cerrar Caja</span>
                    <span wire:loading>Cerrando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Resumen -->
<!-- En gestion-caja.blade.php, MODAL RESUMEN -->
    @if($mostrar_resumen && $resumen)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg w-full max-w-2xl max-h-[90vh] flex flex-col"> <!-- Cambiado a max-w-2xl y flex-col -->
            <!-- Header fijo -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold">Resumen de Caja</h3>
                <p class="text-sm text-gray-600 mt-1">
                    Caja #{{ $caja_activa->id ?? 'N/A' }} | 
                    {{ now()->format('d/m/Y H:i') }}
                </p>
            </div>
            
            <!-- Contenido con scroll -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="space-y-4">
                    <!-- Montos Iniciales -->
                    <div class="p-4 bg-gray-50 rounded-lg border">
                        <h4 class="font-bold mb-2 text-gray-700">💰 Monto Inicial</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="text-gray-600">Bolívares:</div>
                            <div class="text-right font-bold text-gray-800">Bs {{ number_format($resumen['monto_inicial_bs'], 2) }}</div>
                            <div class="text-gray-600">Dólares:</div>
                            <div class="text-right font-bold text-gray-800">$ {{ number_format($resumen['monto_inicial_dolares'], 2) }}</div>
                        </div>
                    </div>
                    
                    <!-- Efectivo Recibido -->
                    <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                        <h4 class="font-bold mb-2 text-green-700">💵 Efectivo Recibido</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="text-gray-600">Efectivo Bs:</div>
                            <div class="text-right font-bold text-green-600">Bs {{ number_format($resumen['ventas_bs'], 2) }}</div>
                            <div class="text-gray-600">Efectivo $:</div>
                            <div class="text-right font-bold text-green-600">$ {{ number_format($resumen['ventas_dolares'], 2) }}</div>
                            @if(($resumen['ventas_usdt'] ?? 0) > 0)
                            <div class="text-gray-600">USDT:</div>
                            <div class="text-right font-bold text-blue-600">$ {{ number_format($resumen['ventas_usdt'], 2) }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Otros métodos de pago -->
                    @if(($resumen['otros_metodos_bs'] ?? 0) > 0 || ($resumen['otros_metodos_dolares'] ?? 0) > 0)
                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h4 class="font-bold mb-2 text-blue-700">💳 Otros Métodos de Pago</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="text-gray-600">Total Bs:</div>
                            <div class="text-right font-bold text-blue-600">Bs {{ number_format($resumen['otros_metodos_bs'], 2) }}</div>
                            <div class="text-gray-600">Total $:</div>
                            <div class="text-right font-bold text-blue-600">$ {{ number_format($resumen['otros_metodos_dolares'], 2) }}</div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Detalle por método de pago -->
                    @if(!empty($resumen['detalle_metodos_pago']))
                    <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                        <h4 class="font-bold mb-3 text-purple-700">📊 Detalle por Método de Pago</h4>
                        <div class="space-y-3">
                            @foreach($resumen['detalle_metodos_pago'] as $metodo => $detalle)
                            <div class="p-3 bg-white rounded border">
                                <div class="font-bold text-sm text-purple-600 mb-2">{{ $metodo }}</div>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div class="text-gray-500">Cantidad:</div>
                                    <div class="text-right font-bold">{{ $detalle['cantidad'] }} ventas</div>
                                    
                                    @if($detalle['pagado_bs'] > 0)
                                    <div class="text-gray-500">Pagado Bs:</div>
                                    <div class="text-right font-bold text-green-600">Bs {{ number_format($detalle['pagado_bs'], 2) }}</div>
                                    @endif
                                    
                                    @if($detalle['pagado_dolares'] > 0)
                                    <div class="text-gray-500">Pagado $:</div>
                                    <div class="text-right font-bold text-green-600">$ {{ number_format($detalle['pagado_dolares'], 2) }}</div>
                                    @endif
                                    
                                    @if($detalle['total_bs'] > 0)
                                    <div class="text-gray-500">Total Bs:</div>
                                    <div class="text-right font-bold">Bs {{ number_format($detalle['total_bs'], 2) }}</div>
                                    @endif
                                    
                                    @if($detalle['total_dolares'] > 0)
                                    <div class="text-gray-500">Total $:</div>
                                    <div class="text-right font-bold">$ {{ number_format($detalle['total_dolares'], 2) }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- Saldos Actuales -->
                    <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                        <h4 class="font-bold mb-2 text-yellow-700">🏦 Saldo en Caja (Efectivo)</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-lg text-gray-700">Bolívares:</span>
                                <span class="text-2xl font-bold text-yellow-600">Bs {{ number_format($resumen['saldo_actual_bs'], 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-lg text-gray-700">Dólares:</span>
                                <span class="text-2xl font-bold text-yellow-600">$ {{ number_format($resumen['saldo_actual_dolares'], 2) }}</span>
                            </div>
                        </div>
                        
                        <!-- Diferencia -->
                        @php
                            $diferencia_bs = $resumen['saldo_actual_bs'] - $resumen['monto_inicial_bs'];
                            $diferencia_dolares = $resumen['saldo_actual_dolares'] - $resumen['monto_inicial_dolares'];
                        @endphp
                        <div class="mt-4 pt-4 border-t border-yellow-300">
                            <h5 class="font-bold mb-2 text-gray-700">📈 Diferencia</h5>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="text-gray-600">Bolívares:</div>
                                <div class="text-right">
                                    <span class="font-bold {{ $diferencia_bs >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $diferencia_bs >= 0 ? '+' : '' }}Bs {{ number_format($diferencia_bs, 2) }}
                                    </span>
                                </div>
                                <div class="text-gray-600">Dólares:</div>
                                <div class="text-right">
                                    <span class="font-bold {{ $diferencia_dolares >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $diferencia_dolares >= 0 ? '+' : '' }}$ {{ number_format($diferencia_dolares, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resumen General -->
                    <div class="p-4 bg-gray-100 rounded-lg border">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-bold text-gray-700">📋 Resumen General</h4>
                                <p class="text-sm text-gray-600">Total de transacciones realizadas</p>
                            </div>
                            <div class="text-3xl font-bold text-gray-800">
                                {{ $resumen['total_ventas'] }}
                                <span class="text-lg font-normal">ventas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer fijo -->
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex justify-between">
                    <button 
                        wire:click="generarPDFResumen"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50"
                    >
                        <span wire:loading.remove>📄 Generar PDF</span>
                        <span wire:loading>Generando PDF...</span>
                    </button>
                    <button 
                        wire:click="$set('mostrar_resumen', false)"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- Botones de depuración -->


    <!-- Debug info (solo para desarrollo) -->
    @if(env('APP_DEBUG'))
    <div class="mt-4 p-3 bg-gray-100 rounded text-xs">
        <strong>Debug Info:</strong><br>
        Caja Activa: {{ $caja_activa ? 'Sí (ID: ' . $caja_activa->id . ')' : 'No' }}<br>
        Monto BS: {{ $monto_inicial_bs }}<br>
        Monto USD: {{ $monto_inicial_dolares }}
    </div>
    @endif

    <!-- Script para descargar PDF - MEJORADO -->
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('descargar-pdf', (data) => {
            console.log('Recibiendo evento para descargar PDF');
            
            try {
                // Crear un enlace invisible para descargar el PDF
                const link = document.createElement('a');
                const byteCharacters = atob(data.pdfContent);
                const byteNumbers = new Array(byteCharacters.length);
                
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], {type: 'application/pdf'});
                const url = URL.createObjectURL(blob);
                
                link.href = url;
                link.download = data.filename;
                link.target = '_blank';
                
                // Añadir al documento y hacer clic
                document.body.appendChild(link);
                link.click();
                
                // Limpiar
                setTimeout(() => {
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                }, 100);
                
                console.log('PDF descargado: ' + data.filename);
                
            } catch (error) {
                console.error('Error al descargar PDF:', error);
                
                // Mostrar mensaje de error
                alert('Error al descargar el PDF: ' + error.message + 
                      '\n\nPor favor, verifica la consola para más detalles.');
            }
        });
    });
</script>
</div>