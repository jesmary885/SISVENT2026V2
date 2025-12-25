<div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-user-shield text-blue-500 mr-3"></i>
            Cambiar Credenciales
        </h2>
        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse" title="Usuario activo"></div>
    </div>

    <!-- Información Actual -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-500 text-xl mr-3"></i>
            <div>
                <h3 class="font-semibold text-blue-800">Información Actual</h3>
                <p class="text-blue-600 text-sm">
                    Usuario: <span class="font-bold">{{ auth()->user()->name }}</span> | 
                    Email: <span class="font-bold">{{ auth()->user()->email }}</span>
                </p>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="cambiarCredenciales">
        <!-- Cambiar Email -->
        <div class="mb-6">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-envelope mr-2 text-blue-500"></i>
                Correo Electrónico
            </label>
            <input
                type="email"
                id="email"
                wire:model="email"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('email') border-red-500 @enderror"
                placeholder="nuevo@email.com"
            >
            @error('email')
                <p class="text-red-500 text-sm mt-1 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Cambiar Contraseña -->
        <div class="space-y-4 mb-6">
            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-key mr-2 text-green-500"></i>
                    Cambiar Contraseña
                </h3>
                
                <!-- Contraseña Actual -->
                <div class="mb-4">
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña Actual
                    </label>
                    <input
                        type="password"
                        id="current_password"
                        wire:model="current_password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('current_password') border-red-500 @enderror"
                        placeholder="Ingresa tu contraseña actual"
                    >
                    @error('current_password')
                        <p class="text-red-500 text-sm mt-1 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Nueva Contraseña -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Nueva Contraseña
                        </label>
                        <input
                            type="password"
                            id="new_password"
                            wire:model="new_password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('new_password') border-red-500 @enderror"
                            placeholder="Mínimo 8 caracteres"
                        >
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Contraseña
                        </label>
                        <input
                            type="password"
                            id="new_password_confirmation"
                            wire:model="new_password_confirmation"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            placeholder="Repite la nueva contraseña"
                        >
                    </div>
                </div>
                @error('new_password')
                    <p class="text-red-500 text-sm mt-1 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        {{ $message }}
                    </p>
                @enderror

                <!-- Indicadores de Seguridad -->
                @if($new_password)
                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-2">Seguridad de la contraseña:</p>
                    <div class="space-y-1">
                        <div class="flex items-center text-sm {{ strlen($new_password) >= 8 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas {{ strlen($new_password) >= 8 ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                            Mínimo 8 caracteres
                        </div>
                        <div class="flex items-center text-sm {{ $new_password === $new_password_confirmation ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas {{ $new_password === $new_password_confirmation ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                            Las contraseñas coinciden
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Botones -->
        <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 px-6 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center justify-center"
            >
                <i class="fas fa-save mr-2"></i>
                <span wire:loading.remove>Actualizar Credenciales</span>
                <span wire:loading>
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Actualizando...
                </span>
            </button>

            <button
                type="button"
                wire:click="$set('open_modal', false)"
                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 px-6 rounded-xl font-semibold transition duration-200 flex items-center justify-center"
            >
                <i class="fas fa-times mr-2"></i>
                Cancelar
            </button>
        </div>
    </form>
</div>

<!-- Script para notificaciones -->
<script>
    document.addEventListener('livewire:load', function() {
        Livewire.on('credenciales-actualizadas', (data) => {
            // Usar notyf o el sistema de notificaciones que tengas
            if (data.type === 'success') {
                alertify.success(data.message);
            } else {
                alertify.error(data.message);
            }
        });
    });
</script>