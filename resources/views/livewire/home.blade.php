 <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
        <!-- Header Elegante -->


        <!-- Stats Cards Mejoradas - Mismas variables -->
        <div class="mx-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 ">
                
                <!-- Ganancias del Día -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl blur opacity-20 group-hover:opacity-30 transition duration-300"></div>
                    <div class="relative bg-white rounded-2xl p-6 shadow-lg border border-purple-100 transform transition-all duration-300 group-hover:scale-105 group-hover:shadow-xl">
                        <div class="flex items-center">
                            <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mr-4 shadow-md">
                                <i class="fas fa-chart-line text-white text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-gray-800 text-lg">Bs {{$ganancia_dia_bs}}</span>
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-1 rounded border border-yellow-300">
                                        $ {{$ganancia_dia_dol}}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500 font-medium">Ganancias del día</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ganancias del Mes -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl blur opacity-20 group-hover:opacity-30 transition duration-300"></div>
                    <div class="relative bg-white rounded-2xl p-6 shadow-lg border border-blue-100 transform transition-all duration-300 group-hover:scale-105 group-hover:shadow-xl">
                        <div class="flex items-center">
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mr-4 shadow-md">
                                <i class="fas fa-calendar-alt text-white text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-gray-800 text-lg">Bs {{$ganancia_mes_bs}}</span>
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-1 rounded border border-yellow-300">
                                        $ {{$ganancia_mes_dol}}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500 font-medium">Ganancias del mes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ventas del Día -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl blur opacity-20 group-hover:opacity-30 transition duration-300"></div>
                    <div class="relative bg-white rounded-2xl p-6 shadow-lg border border-green-100 transform transition-all duration-300 group-hover:scale-105 group-hover:shadow-xl">
                        <div class="flex items-center">
                            <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mr-4 shadow-md">
                                <i class="fas fa-shopping-cart text-white text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-2xl font-bold text-gray-800 mb-1">{{$ventas_dia}}</div>
                                <div class="text-sm text-gray-500 font-medium">Ventas del día</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tasa del Día -->

                <a href="{{ route('configuracion') }}" class="group relative">
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-red-600 rounded-2xl blur opacity-20 group-hover:opacity-30 transition duration-300"></div>
                        <div class="relative bg-white rounded-2xl p-6 shadow-lg border border-red-100 transform transition-all duration-300 group-hover:scale-105 group-hover:shadow-xl">
                            <div class="flex items-center">
                                <div class="w-14 h-14 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center mr-4 shadow-md">
                                    <i class="fas fa-exchange-alt text-white text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-gray-800 mb-1">{{$tasa_dia}}</div>
                                    <div class="text-sm text-gray-500 font-medium">Tasa Bs / USD</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </a>

            </div>

            <!-- Secciones adicionales opcionales -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Ventas Recientes -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-star text-yellow-600   mr-2"></i>
                        Productos más vendidos
                    </h3>
                    <div class="space-y-3">

                       <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
    <!-- Header de la tabla -->
                        <div class="bg-blue-600 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-trophy text-white text-xl mr-3"></i>
                                    <h2 class="text-xl font-bold text-white">Productos Más Vendidos</h2>
                                </div>
                                <span class="bg-white text-purple-600 px-3 py-1 rounded-full text-sm font-bold">
                                    {{ $productosMasVendidos->count() }} productos
                                </span>
                            </div>
                        </div>

            @if($productosMasVendidos->isNotEmpty())
                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                    
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">Producto</th>
                                <th class="px-6 py-4 text-center font-semibold text-gray-700">Unidades Vendidas</th>
                                <th class="px-6 py-4 text-center font-semibold text-gray-700">Ingresos</th>
                    
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($productosMasVendidos as $index => $producto)
                            <tr class="hover:bg-purple-50 transition-colors duration-200 group">
                                <!-- Ranking -->
            
                                
                                <!-- Producto -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                            <i class="fas fa-box text-white text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-800 group-hover:text-purple-600 truncate max-w-[200px]" title="{{ $producto['nombre'] }}">
                                                {{ $producto['nombre'] }}
                                            </p>
                                            <p class="text-xs text-gray-500 truncate max-w-[200px]">
                                                {{ Str::limit($producto['codigo'] ?? 'N/A', 30) }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Unidades Vendidas -->
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                            <i class="fas fa-chart-line mr-2"></i>
                                            {{ $producto['total_vendido'] }} und
                                        </span>
                                        <div class="w-24 bg-gray-200 rounded-full h-2 mt-2">
                                            @php
                                                $maxVentas = $productosMasVendidos->max('total_vendido');
                                                $porcentaje = $maxVentas > 0 ? ($producto['total_vendido'] / $maxVentas) * 100 : 0;
                                            @endphp
                                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $porcentaje }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Ingresos -->
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="font-bold text-gray-800 text-lg">
                                            ${{ number_format($producto['total_ingresos'], 2) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            Ingresos totales
                                        </span>
                                    </div>
                                </td>
                                
                
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer con estadísticas -->
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <div class="flex flex-wrap justify-between items-center text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span>Total vendido: {{ $productosMasVendidos->sum('total_vendido') }} unidades</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-dollar-sign mr-2"></i>
                            <span>Ingresos totales: ${{ number_format($productosMasVendidos->sum('total_ingresos'), 2) }}</span>
                        </div>
                    </div>
                </div>
            @else
                <!-- Estado vacío -->
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-bar text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">No hay datos de ventas</h3>
                    <p class="text-gray-500 mb-4">No se registraron ventas en el período seleccionado</p>
                    <button class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-plus mr-2"></i>
                        Agregar Productos
                    </button>
                </div>
            @endif
</div>
                        {{-- <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-users text-white text-xs"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800">Cliente frecuente</div>
                                    <div class="text-xs text-gray-500">Hace 1 hora</div>
                                </div>
                            </div>
                            <div class="text-green-600 font-bold text-sm">+$120</div>
                        </div> --}}
                    </div>
                </div>

               <!-- Acciones Rápidas Mejoradas -->
<div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-bolt text-yellow-500 mr-2"></i>
        Acciones Rápidas
    </h3>
    <div class="grid grid-cols-1 gap-4">
        <a href="{{ route('ventas_index') }}" class="p-4 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center justify-between group">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-plus-circle text-white text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-white text-lg">Nueva Venta</p>
                    <p class="text-blue-100 text-sm">Iniciar transacción</p>
                </div>
            </div>
            <i class="fas fa-arrow-right text-white text-lg group-hover:translate-x-1 transition-transform"></i>
        </a>

        <a href="{{ route('inventario_index') }}" class="p-4 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center justify-between group">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-boxes text-white text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-white text-lg">Gestionar Inventario</p>
                    <p class="text-green-100 text-sm">Ver stock y productos</p>
                </div>
            </div>
            <i class="fas fa-arrow-right text-white text-lg group-hover:translate-x-1 transition-transform"></i>
        </a>

        @can('Administrador')

        <a href="{{ route('reportes') }}" class="p-4 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center justify-between group">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-chart-bar text-white text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-white text-lg">Ver Reportes</p>
                    <p class="text-white text-sm">Análisis y estadísticas</p>
                </div>
            </div>
            <i class="fas fa-arrow-right text-white text-lg group-hover:translate-x-1 transition-transform"></i>
        </a>

        @endcan

        <!-- En algún lugar visible, por ejemplo cerca del header -->
{{-- @if($tieneVentaPausada)
<div class="mb-4 p-3 bg-yellow-100 border border-yellow-400 rounded-lg">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <i class="fas fa-pause text-yellow-500 mr-2"></i>
            <div>
                <span class="font-bold text-yellow-800">Venta en pausa:</span>
                <span class="text-yellow-700 ml-2">{{ $ventaPausadaInfo['cliente_nombre'] ?? 'Cliente' }}</span>
                <span class="text-yellow-600 text-sm ml-2">({{ $ventaPausadaInfo['mesa_ubicacion'] ?? 'Sin ubicación' }})</span>
            </div>
        </div>
        <button 
            wire:click="reanudarVentaPausada"
            class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600"
        >
            Reanudar
        </button>
    </div>
</div>
@endif --}}

    </div>
</div>
            </div>
        </div>
    </div>

    <style>
        /* Animaciones suaves */
        .group:hover .transform {
            transform: translateY(-2px);
        }
    </style>