<div class="w-full">
    <div class="flex items-center gap-1">
        <!-- Botón decremento -->
        <button 
            class="w-8 h-8 bg-gray-800 text-amber-300 rounded-lg flex items-center justify-center text-lg font-bold hover:bg-gray-900 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            wire:loading.attr="disabled"
            wire:target="decrement,addItem"
            wire:click="decrement"
            @if($qty <= 1 || !$caja_abierta || $esperando_venta) disabled @endif
        >
            <span wire:loading.remove wire:target="decrement">-</span>
            <span wire:loading wire:target="decrement">...</span>
        </button>
        
        <!-- Input -->
        <input 
            wire:model="qty" 
            type="number" 
            min="1" 
            max="{{ $quantity }}" 
            class="w-10 h-8 text-xs text-center text-gray-800 bg-white border border-amber-300 rounded focus:outline-none focus:border-amber-500 @if(!$caja_abierta || $esperando_venta) opacity-50 cursor-not-allowed @endif"
            @if(!$caja_abierta || $esperando_venta) disabled @endif
        >
        
        <!-- Botón incremento -->
        <button 
            class="w-8 h-8 bg-gray-800 text-amber-300 rounded-lg flex items-center justify-center text-lg font-bold hover:bg-gray-900 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            wire:loading.attr="disabled"
            wire:target="increment,addItem"
            wire:click="increment"
            @if($qty >= $quantity || !$caja_abierta || $esperando_venta) disabled @endif
        >
            <span wire:loading.remove wire:target="increment">+</span>
            <span wire:loading wire:target="increment">...</span>
        </button>
        
        <!-- Botón agregar -->
        <button 
            class="w-20 h-8 bg-gray-800 text-amber-300 text-xs font-bold rounded-lg hover:bg-gray-900 transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
            wire:click="addItem"
            wire:loading.attr="disabled"
            wire:target="addItem"
            @if(!$caja_abierta || $qty > $quantity || $qty <= 0 || $esperando_venta) disabled @endif
        >
            <span wire:loading.remove wire:target="addItem">
                @if($esperando_venta)
                    <i class="fas fa-spinner fa-spin text-xs"></i>
                @elseif(!$caja_abierta)
                    <i class="fas fa-lock text-xs"></i>
                @else
                    Agregar
                @endif
            </span>
            <span wire:loading wire:target="addItem">
                <i class="fas fa-spinner fa-spin text-xs"></i>
            </span>
        </button>
    </div>

    <!-- Indicadores de estado -->
    <div class="mt-1 text-xs">
        @if($esperando_venta)
            <div class="text-blue-500 flex items-center">
                <i class="fas fa-spinner fa-spin mr-1"></i>
                <span>Creando venta...</span>
            </div>
        @elseif(!$caja_abierta)
            <div class="text-red-500 flex items-center">
                <i class="fas fa-lock mr-1"></i>
                <span>Caja cerrada</span>
            </div>
        @elseif(!$venta_activa_id)
            <div class="text-green-500 flex items-center">
                <i class="fas fa-plus mr-1"></i>
                <span>Click en Agregar para iniciar</span>
            </div>
        @else
            <div class="text-green-500 flex items-center">
                <i class="fas fa-check mr-1"></i>
                <span>Stock: {{ $quantity }}</span>
            </div>
        @endif
    </div>
</div>