<div class="space-y-6">
    <!-- Filtros por Fecha -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
        <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-chart-pie text-purple-500 mr-2"></i>
                Reporte Completo de Ventas
            </h2>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Desde:</label>
                    <input type="date" wire:model.live="fechaInicio" 
                           class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Hasta:</label>
                    <input type="date" wire:model.live="fechaFin" 
                           class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Botón de exportación -->
            <button wire:click="exportarPDF" 
                    wire:loading.attr="disabled"
                    class="bg-green-600 hover:bg-green-700 cursor-pointer text-white px-4 py-2 rounded-lg font-semibold transition duration-200 flex items-center space-x-2">
                <i class="fas fa-file-pdf"></i>
                <span wire:loading.remove>Exportar a PDF</span>
                <span wire:loading>
                    <i class="fas fa-spinner fa-spin"></i>
                    Generando...
                </span>
            </button>
        </div>
    </div>

    <!-- MÉTRICAS PRINCIPALES SIMPLIFICADAS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Ventas Totales -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total de Ventas</p>
                    <p class="text-2xl font-bold">{{ number_format($totalVentasPeriodo) }}</p>
                    <p class="text-blue-100 text-xs mt-1">transacciones</p>
                </div>
                <i class="fas fa-shopping-cart text-2xl opacity-80"></i>
            </div>
        </div>

        <!-- Ingresos Totales -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Ingresos Totales</p>
                    <p class="text-2xl font-bold">${{ number_format($ingresosTotales, 2) }}</p>
                    <p class="text-green-100 text-xs mt-1">en dólares</p>
                </div>
                <i class="fas fa-money-bill-wave text-2xl opacity-80"></i>
            </div>
        </div>

        <!-- Ganancia Estimada -->
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm">Ganancia Bruta</p>
                    <p class="text-2xl font-bold">${{ number_format(abs($gananciaBruta), 2) }}</p>
                    <p class="card-text">{{ $gananciaBruta >= 0 ? 'GANANCIA BRUTA' : 'PÉRDIDA BRUTA' }}</p>
                </div>
                <i class="fas fa-chart-line text-2xl opacity-80"></i>
            </div>
        </div>
    </div>

    <!-- DESGLOSE DE EGRESOS (nueva sección) -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-money-bill-wave text-red-500 mr-2"></i>
            Desglose de Egresos (Inversiones)
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Costo de Ventas -->
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-800 font-semibold">Costo de Ventas</p>
                        <p class="text-red-600 text-sm">Productos vendidos</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-red-700">${{ number_format($desgloseEgresos['costo_ventas'] ?? 0, 2) }}</p>
                        <p class="text-red-600 text-xs">costo real</p>
                    </div>
                </div>
            </div>
            
            <!-- Compras del Negocio -->
     
            <!-- Total Egresos -->
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-800 font-semibold">Total Egresos</p>
                        <p class="text-purple-600 text-sm">Inversión total</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-purple-700">${{ number_format($egresosTotales, 2) }}</p>
                        <p class="text-purple-600 text-xs">Costo + Compras</p>
                    </div>
                </div>
            </div>
        </div>
        
        @if($desgloseEgresos['total_compras_bolivares'] > 0)
        <div class="mt-4 text-center text-sm text-gray-600">
            <i class="fas fa-info-circle"></i>
            Compras en Bolívares: Bs. {{ number_format($desgloseEgresos['total_compras_bolivares'], 2) }}
        </div>
        @endif
    </div>

    <!-- Detalle de Compras Realizadas -->
<!-- SECCIÓN DE COMPRAS DEL NEGOCIO (NUEVA) -->
<div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200 mt-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-shopping-cart text-purple-500 mr-2"></i>
        Compras del Negocio (Detalle)
    </h3>
    
    <!-- Resumen de compras -->
    <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mb-6">
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-800 font-semibold">Total Compras</p>
                    <p class="text-purple-600 text-sm">{{ $totalComprasPeriodo }} compras</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-purple-700">
                        ${{ number_format($desgloseEgresos['compras_negocio'] ?? $desgloseEgresos['gasto_compras'] ?? 0, 2) }}
                    </p>
                    <p class="text-purple-600 text-xs">inversión en inventario</p>
                </div>
            </div>
        </div>
        
  
    </div>
    
    <!-- Tabla de detalle de compras -->
    @if($totalComprasPeriodo > 0 && !empty($detalleCompras) && $detalleCompras->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Fecha</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Producto</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Cantidad</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Precio Compra</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Total</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Proveedor</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Registrado por</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($detalleCompras as $compra)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4">
                        {{ \Carbon\Carbon::parse($compra->created_at)->format('d/m/Y H:i') }}
                    </td>
                    <td class="py-3 px-4">
                        <div class="font-medium text-gray-900">
                            {{ $compra->producto->nombre ?? 'N/A' }}
                        </div>
                        @if($compra->producto->codigo ?? false)
                        <div class="text-xs text-gray-500">
                            Cod: {{ $compra->producto->codigo }}
                        </div>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center font-semibold">
                        {{ $compra->cantidad }}
                    </td>
                    <td class="py-3 px-4">
                        @if($compra->precio_compra_dolares > 0)
                        <div class="text-green-600 font-medium">
                            ${{ number_format($compra->precio_compra_dolares, 2) }}
                        </div>
                        @endif
                        @if($compra->precio_compra_bolivares > 0)
                        <div class="text-xs text-gray-500">
                            Bs. {{ number_format($compra->precio_compra_bolivares, 2) }}
                        </div>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        @php
                            $totalDolares = ($compra->total_pagado_dolares > 0) 
                                ? $compra->total_pagado_dolares 
                                : ($compra->precio_compra_dolares * $compra->cantidad);
                        @endphp
                        
                        <div class="font-bold text-purple-600">
                            ${{ number_format($totalDolares, 2) }}
                        </div>
                        
                        @if($compra->total_pagado_bolivares > 0)
                        <div class="text-xs text-gray-500">
                            Bs. {{ number_format($compra->total_pagado_bolivares, 2) }}
                        </div>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <div class="font-medium">
                            {{ $compra->proveedor->nombre ?? 'N/A' }}
                        </div>
                        @if($compra->proveedor->telefono ?? false)
                        <div class="text-xs text-gray-500">
                            {{ $compra->proveedor->telefono }}
                        </div>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        {{ $compra->user->name ?? 'Sistema' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="4" class="py-3 px-4 text-right font-bold">
                        TOTALES:
                    </td>
                    <td class="py-3 px-4 font-bold text-purple-700">
                        ${{ number_format($desgloseEgresos['compras_negocio'] ?? $desgloseEgresos['gasto_compras'] ?? 0, 2) }}
                        @if($desgloseEgresos['total_compras_bolivares'] ?? 0 > 0)
                        <div class="text-xs font-normal text-blue-600">
                            Bs. {{ number_format($desgloseEgresos['total_compras_bolivares'], 2) }}
                        </div>
                        @endif
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="text-center py-8 text-gray-500">
        <i class="fas fa-shopping-cart text-3xl mb-2"></i>
        <p>No se realizaron compras en este período</p>
        <p class="text-sm text-gray-400 mt-1">
            {{ date('d/m/Y', strtotime($fechaInicio)) }} - {{ date('d/m/Y', strtotime($fechaFin)) }}
        </p>
    </div>
    @endif
</div>

    <!-- DEUDAS DEL PERÍODO (SECCIÓN MÁS IMPORTANTE) -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-money-check-alt text-red-500 mr-2"></i>
            Estado de Deudas
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Deudas Pendientes -->
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-800 font-semibold">Deudas Pendientes</p>
                        <p class="text-red-600 text-sm">{{ $estadoDeudas['pendientes']['cantidad'] ?? 0 }} deudas</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-red-700">${{ number_format($deudasPendientesTotal, 2) }}</p>
                        <p class="text-red-600 text-xs">por cobrar</p>
                    </div>
                </div>
            </div>
            
            <!-- Deudas Pagadas -->
            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-800 font-semibold">Deudas Pagadas</p>
                        <p class="text-green-600 text-sm">{{ $estadoDeudas['pagadas']['cantidad'] ?? 0 }} deudas</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-green-700">${{ number_format($deudasPagadasTotal, 2) }}</p>
                        <p class="text-green-600 text-xs">recuperadas</p>
                    </div>
                </div>
            </div>
            
            <!-- Total Deudas -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-800 font-semibold">Total Deudas</p>
                        <p class="text-blue-600 text-sm">período completo</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-blue-700">${{ number_format($totalDeudas, 2) }}</p>
                        <p class="text-blue-600 text-xs">registradas</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detalle de Deudas -->
        @if(count($detalleDeudas) > 0)
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Cliente</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Monto</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Estado</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Fecha Límite</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Venta #</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($detalleDeudas as $deuda)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4">{{ $deuda['cliente'] }}</td>
                        <td class="py-3 px-4 font-semibold">${{ number_format($deuda['monto'], 2) }}</td>
                        <td class="py-3 px-4">
                            @if($deuda['estado'] == 'pendiente')
                                @if($deuda['dias_vencimiento'] < 0)
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">
                                        VENCIDO
                                    </span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-bold">
                                        PENDIENTE
                                    </span>
                                @endif
                            @else
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">
                                    PAGADA
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            {{ \Carbon\Carbon::parse($deuda['fecha_limite'])->format('d/m/Y') }}
                            @if($deuda['estado'] == 'pendiente' && $deuda['dias_vencimiento'] < 0)
                                <p class="text-red-600 text-xs mt-1">
                                    Vencido hace {{ abs($deuda['dias_vencimiento']) }} días
                                </p>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                #{{ $deuda['venta_id'] }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-money-check text-3xl mb-2"></i>
            <p>No hay deudas registradas en este período</p>
        </div>
        @endif
    </div>

    <!-- PRODUCTOS MÁS VENDIDOS -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-trophy text-yellow-500 mr-2"></i>
            Productos Más Vendidos
        </h3>
        
        @if(count($productosMasVendidos) > 0)
        <div class="space-y-3">
            @foreach($productosMasVendidos as $index => $producto)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center mr-4">
                        <span class="text-white font-bold">{{ $index + 1 }}</span>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">{{ $producto->nombre }}</div>
                        <div class="text-sm text-gray-600">
                            {{ number_format($producto->unidades_vendidas) }} unidades
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-green-600">${{ number_format($producto->total_generado, 2) }}</div>
                    <div class="text-sm text-gray-500">total generado</div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-box-open text-3xl mb-2"></i>
            <p>No hay datos de productos vendidos</p>
        </div>
        @endif
    </div>

    <!-- VENTAS POR MÉTODO DE PAGO Y CLIENTES TOP -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Métodos de Pago -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-credit-card text-purple-500 mr-2"></i>
                Ventas por Método de Pago
            </h3>
            <div class="space-y-3">
                @forelse($ventasPorMetodoPago as $metodo)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-money-bill text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800 capitalize">
                                @if($metodo->metodo_pago == 'dol_efec')
                                    Dólares Efectivo
                                @elseif($metodo->metodo_pago == 'bs_efec')
                                    Bolívares Efectivo
                                @elseif($metodo->metodo_pago == 'debito')
                                    Tarjeta Débito
                                @elseif($metodo->metodo_pago == 'pago_movil')
                                    Pago Móvil
                                @elseif($metodo->metodo_pago == 'USDT')
                                    USDT
                                @else
                                    {{ $metodo->metodo_pago }}
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">{{ $metodo->cantidad_ventas }} ventas</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-purple-600">${{ number_format($metodo->total_dolares, 2) }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $ingresosTotales > 0 ? number_format(($metodo->total_dolares / $ingresosTotales) * 100, 1) : 0 }}%
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500">
                    <p>No hay datos de métodos de pago</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Clientes Top -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-users text-blue-500 mr-2"></i>
                Clientes que Más Compran
            </h3>
            <div class="space-y-3">
                @forelse($topClientes as $cliente)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800">{{ $cliente->nombre }}</div>
                            @if($cliente->telefono)
                            <div class="text-xs text-gray-500">{{ $cliente->telefono }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-blue-600">${{ number_format($cliente->total_gastado, 2) }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $cliente->cantidad_compras }} compras
                            @if($cliente->deuda_actual > 0)
                            <span class="text-red-500 ml-1">(${{ number_format($cliente->deuda_actual, 2) }})</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500">
                    <p>No hay datos de clientes</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- VENTAS POR DÍA (gráfica simple) -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-calendar-alt text-green-500 mr-2"></i>
            Ventas por Día
        </h3>
        @if(count($ventasPorDia) > 0)
        <div class="space-y-3">
            @foreach($ventasPorDia as $dia)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="font-semibold text-gray-800">{{ $dia['fecha'] }}</div>
                <div class="flex items-center space-x-6">
                    <div class="text-center">
                        <div class="text-sm text-gray-500">Ventas</div>
                        <div class="font-bold text-gray-800">{{ $dia['ventas'] }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-gray-500">Total</div>
                        <div class="font-bold text-green-600">${{ number_format($dia['total'], 2) }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-calendar-times text-3xl mb-2"></i>
            <p>No hay datos de ventas por día</p>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('livewire:init', function () {
    // Actualizar automáticamente cuando cambian las fechas
    Livewire.on('updatedFechaInicio', function () {
        setTimeout(() => {
            Livewire.dispatch('refresh');
        }, 300);
    });
    
    Livewire.on('updatedFechaFin', function () {
        setTimeout(() => {
            Livewire.dispatch('refresh');
        }, 300);
    });
});
</script>