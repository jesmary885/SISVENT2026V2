<div>
    <style>
        .badge-pendiente {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-pagada {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-cancelada {
            background-color: #f3f4f6;
            color: #374151;
        }
        .badge-vencida {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .estado-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .table-row-hover:hover {
            background-color: #f9fafb;
        }
        .filter-card {
            transition: all 0.3s ease;
        }
        .filter-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>

    <!-- MODAL PARA EDITAR DEUDA -->
    @if($showEditModal && $deuda_seleccionada)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-edit mr-2"></i>
                    Editar Deuda
                </h3>
                <button wire:click="cerrarModales" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="bg-blue-50 p-3 rounded border border-blue-200">
                    <p class="text-sm font-medium text-blue-800">
                        Cliente: <span class="font-bold">{{ $deuda_seleccionada->cliente->nombre }}</span>
                    </p>
                    <p class="text-xs text-blue-600">
                        Venta #{{ $deuda_seleccionada->venta_id }} | 
                        Fecha: {{ $deuda_seleccionada->created_at->format('d/m/Y') }}
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Monto en Dólares *
                        </label>
                        <input type="number" step="0.01" min="0"
                            wire:model="monto_dolares_edit"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('monto_dolares_edit') 
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Monto en Bolívares *
                        </label>
                        <input type="number" step="0.01" min="0"
                            wire:model="monto_bolivares_edit"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('monto_bolivares_edit') 
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha Límite *
                        </label>
                        <input type="date"
                            wire:model="fecha_limite_edit"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('fecha_limite_edit') 
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Estado *
                        </label>
                        <select wire:model="estado_edit"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="pendiente">Pendiente</option>
                            <option value="pagada">Pagada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                        @error('estado_edit') 
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Comentario
                    </label>
                    <textarea rows="3"
                        wire:model="comentario_edit"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Observaciones sobre la deuda..."></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button wire:click="cerrarModales"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button wire:click="actualizarDeuda"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL PARA REGISTRAR PAGO -->
    @if($showPaymentModal && $deuda_seleccionada)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-green-800">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Registrar Pago de Deuda
                </h3>
                <button wire:click="cerrarModales" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="bg-green-50 p-3 rounded border border-green-200">
                    <p class="text-sm font-medium text-green-800">
                        Cliente: <span class="font-bold">{{ $deuda_seleccionada->cliente->nombre }}</span>
                    </p>
                    <p class="text-sm text-green-700">
                        Monto a pagar: 
                        <span class="font-bold">${{ number_format($deuda_seleccionada->monto_dolares, 2) }}</span>
                        <span class="text-green-600">(Bs {{ number_format($deuda_seleccionada->monto_bolivares, 2) }})</span>
                    </p>
                    <p class="text-xs text-green-600">
                        Vencía: {{ $deuda_seleccionada->fecha_limite->format('d/m/Y') }}
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha de Pago *
                        </label>
                        <input type="date"
                            wire:model="fecha_pago"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('fecha_pago') 
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Método de Pago *
                        </label>
                        <select wire:model="metodo_pago"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="efectivo">Efectivo</option>
                            <option value="debito">Débito</option>
                            <option value="pago_movil">Pago Móvil</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="otro">Otro</option>
                        </select>
                        @error('metodo_pago') 
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Comentario del Pago
                    </label>
                    <textarea rows="3"
                        wire:model="comentario_pago"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Ej: Pagó en efectivo, dejó recibo #123..."></textarea>
                </div>
                
                <div class="bg-yellow-50 p-3 rounded border border-yellow-200">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Al registrar el pago, la deuda se marcará como <span class="font-bold">PAGADA</span> 
                        y se actualizará el estado de la venta asociada.
                    </p>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button wire:click="cerrarModales"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button wire:click="registrarPago"
                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-check-circle mr-2"></i>
                    Registrar Pago
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container mx-auto px-4 py-6">
        <!-- HEADER -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-hand-holding-usd mr-3"></i>
                        Gestión de Deudas
                    </h1>
                    <p class="text-gray-600">Administre las deudas pendientes y registre pagos</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Total deudas: {{ $total_deudas_pendientes }}</p>
                    <p class="text-lg font-bold text-red-600">
                        ${{ number_format($total_monto_pendiente_dol, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ESTADÍSTICAS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="rounded-full bg-blue-100 p-3 mr-4">
                        <i class="fas fa-clock text-blue-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Pendientes</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $total_deudas_pendientes }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="rounded-full bg-green-100 p-3 mr-4">
                        <i class="fas fa-dollar-sign text-green-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Monto USD</p>
                        <p class="text-2xl font-bold text-green-600">${{ number_format($total_monto_pendiente_dol, 2) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="rounded-full bg-yellow-100 p-3 mr-4">
                        <i class="fas fa-bolivares text-yellow-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Monto Bs</p>
                        <p class="text-2xl font-bold text-yellow-600">Bs {{ number_format($total_monto_pendiente_bs, 2) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="rounded-full bg-red-100 p-3 mr-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Vencidas</p>
                        <p class="text-2xl font-bold text-red-600">{{ $deudas_vencidas_count }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="bg-white rounded-lg shadow mb-6 filter-card">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-700">
                    <i class="fas fa-filter mr-2"></i>
                    Filtros de Búsqueda
                </h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Búsqueda general -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" wire:model.debounce.300ms="search"
                                class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Cliente, teléfono, ID...">
                        </div>
                    </div>
                    
                    <!-- Filtro por estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select wire:model="filtro_estado"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="pagada">Pagada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    
                    <!-- Filtro por cliente -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                        <select wire:model="filtro_cliente"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos los clientes</option>
                            @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Filtro por fecha -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Límite</label>
                        <select wire:model="filtro_fecha"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todas</option>
                            <option value="vencidas">Vencidas</option>
                            <option value="hoy">Vencen hoy</option>
                            <option value="semana">Próxima semana</option>
                        </select>
                    </div>
                </div>
                
                <!-- Rango de fechas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                        <input type="date" wire:model="desde_fecha"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                        <input type="date" wire:model="hasta_fecha"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button wire:click="$set(['desde_fecha' => '', 'hasta_fecha' => ''])"
                            class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 border border-gray-300">
                            <i class="fas fa-times mr-2"></i>
                            Limpiar Fechas
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE DEUDAS -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-700">
                    <i class="fas fa-list mr-2"></i>
                    Lista de Deudas
                </h2>
                <div class="text-sm text-gray-500">
                    Mostrando {{ $deudas->firstItem() }} - {{ $deudas->lastItem() }} de {{ $deudas->total() }} deudas
                </div>
            </div>
            
            @if($deudas->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID / Cliente
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Monto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fechas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Venta
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($deudas as $deuda)
                        @php
                            $estaVencida = $deuda->estado == 'pendiente' && $deuda->fecha_limite < now();
                            $badgeClass = 'badge-' . $deuda->estado;
                            if ($estaVencida) {
                                $badgeClass = 'badge-vencida';
                            }
                        @endphp
                        <tr class="table-row-hover {{ $estaVencida ? 'bg-red-50' : '' }}">
                            <!-- Cliente -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    #{{ $deuda->id }}
                                </div>
                                <div class="text-sm text-gray-900 font-semibold">
                                    {{ $deuda->cliente->nombre }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    <i class="fas fa-phone mr-1"></i>{{ $deuda->cliente->telefono }}
                                </div>
                            </td>
                            
                            <!-- Monto -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-lg font-bold text-gray-900">
                                    ${{ number_format($deuda->monto_dolares, 2) }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Bs {{ number_format($deuda->monto_bolivares, 2) }}
                                </div>
                                @if($deuda->comentario)
                                <div class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-comment mr-1"></i>
                                    {{ Str::limit($deuda->comentario, 50) }}
                                </div>
                                @endif
                            </td>
                            
                            <!-- Fechas -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    <div class="text-gray-900">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        Límite: {{ $deuda->fecha_limite->format('d/m/Y') }}
                                    </div>
                                    <div class="text-gray-500 text-xs">
                                        <i class="far fa-clock mr-1"></i>
                                        Registrada: {{ $deuda->created_at->format('d/m/Y') }}
                                    </div>
                                    @if($deuda->fecha_pago)
                                    <div class="text-green-600 text-xs">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Pagada: {{ $deuda->fecha_pago->format('d/m/Y') }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Estado -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="estado-badge {{ $badgeClass }}">
                                    @if($estaVencida)
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    VENCIDA
                                    @elseif($deuda->estado == 'pendiente')
                                    <i class="fas fa-clock mr-1"></i>
                                    PENDIENTE
                                    @elseif($deuda->estado == 'pagada')
                                    <i class="fas fa-check-circle mr-1"></i>
                                    PAGADA
                                    @else
                                    <i class="fas fa-ban mr-1"></i>
                                    CANCELADA
                                    @endif
                                </span>
                                
                                @if($estaVencida)
                                <div class="text-xs text-red-600 mt-1">
                                    <i class="fas fa-calendar-times mr-1"></i>
                                    Vencida hace {{ $deuda->fecha_limite->diffForHumans() }}
                                </div>
                                @endif
                            </td>
                            
                            <!-- Venta -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{-- <div class="text-sm text-gray-900">
                                    <a href="{{ route('ventas.show', $deuda->venta_id) }}" 
                                       class="text-blue-600 hover:text-blue-900 hover:underline">
                                        <i class="fas fa-receipt mr-1"></i>
                                        Venta #{{ $deuda->venta_id }}
                                    </a>
                                </div> --}}
                                <div class="text-xs text-gray-500">
                                    <i class="fas fa-user mr-1"></i>
                                    {{ $deuda->usuario->name ?? 'Sistema' }}
                                </div>
                                @if($deuda->comentario_pago)
                                <div class="text-xs text-green-600 mt-1">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i>
                                    {{ Str::limit($deuda->comentario_pago, 40) }}
                                </div>
                                @endif
                            </td>
                            
                            <!-- Acciones -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex flex-col space-y-2">
                                    @if($deuda->estado == 'pendiente')
                                    <button wire:click="abrirModalPago({{ $deuda->id }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-800 rounded-lg hover:bg-green-200 text-sm">
                                        <i class="fas fa-money-bill-wave mr-1"></i>
                                        Registrar Pago
                                    </button>
                                    @endif
                                    
                                    <button wire:click="abrirModalEditar({{ $deuda->id }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 text-sm">
                                        <i class="fas fa-edit mr-1"></i>
                                        Editar
                                    </button>
                                    
                                    @if($deuda->estado == 'pendiente')
                                    <button wire:click="cancelarDeuda({{ $deuda->id }})"
                                        onclick="return confirm('¿Está seguro de cancelar esta deuda?')"
                                        class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 text-sm">
                                        <i class="fas fa-ban mr-1"></i>
                                        Cancelar Deuda
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $deudas->links() }}
            </div>
            
            @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-5xl mb-4">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-700 mb-2">No hay deudas registradas</h3>
                <p class="text-gray-500">
                    @if($search || $filtro_estado || $filtro_cliente || $filtro_fecha)
                        No se encontraron deudas con los filtros aplicados.
                    @else
                        No hay deudas pendientes en el sistema.
                    @endif
                </p>
            </div>
            @endif
        </div>

        <!-- MENSAJES FLASH -->
        @if(session()->has('message'))
        <div class="fixed bottom-4 right-4 z-50" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <span>{{ session('message') }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        // Auto-cerrar mensajes después de 5 segundos
        document.addEventListener('livewire:load', function() {
            setTimeout(() => {
                const flashMessages = document.querySelectorAll('[x-data*="show"]');
                flashMessages.forEach(msg => {
                    if (msg.__x) {
                        msg.__x.$data.show = false;
                    }
                });
            }, 5000);
        });
    </script>
</div>