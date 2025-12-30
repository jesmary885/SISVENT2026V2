<div class="bg-white rounded-xl shadow-lg max-w-2xl w-full">
    <!-- Header -->
    <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    {{ $roleId ? 'Editar Rol' : 'Nuevo Rol' }}
                </h2>
            </div>
            <button type="button" 
                    wire:click="close"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Form -->
     <form wire:submit.prevent="save" class="p-6">
        <!-- Basic Info -->
        <div class="space-y-6">
            <!-- Role Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre del Rol *
                </label>
                <input type="text" 
                       id="name"
                       wire:model="name"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Ej: Vendedor, Supervisor">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Guard Name -->
            <div>
                <label for="guard_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Guard *
                </label>
                <select id="guard_name" 
                        wire:model="guard_name"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="web">Web</option>
                    <option value="api">API</option>
                </select>
                @error('guard_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Permissions -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Permisos</h3>
                
                @if($permissions->count() > 0)
                    <div class="space-y-2 max-h-60 overflow-y-auto p-2 border border-gray-200 rounded-lg">
                        @foreach($permissions as $permission)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="perm_{{ $permission->id }}"
                                       wire:model="selectedPermissions"
                                       value="{{ $permission->id }}"
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="perm_{{ $permission->id }}" class="ml-2 text-sm text-gray-700">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-600">No hay permisos disponibles</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="border-t border-gray-200 pt-6 flex justify-end gap-3 mt-6">
            <button type="button"
                    wire:click="close"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancelar
            </button>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{ $roleId ? 'Actualizar' : 'Crear' }}
            </button>
        </div>
    </form>
</div>