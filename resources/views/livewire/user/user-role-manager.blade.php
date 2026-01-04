<div class="w-full p-4">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Gestión de Usuarios y Roles</h1>
                <p class="text-gray-600">Administra los usuarios y sus roles en el sistema</p>
            </div>
            
            <div class="flex gap-2">
                <!-- Botón Crear Usuario -->
                <button wire:click="createUser" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                    <i class="fas fa-user-plus"></i>
                    Nuevo Usuario
                </button>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="relative">
            <input type="text" 
                   wire:model.live="search" 
                   placeholder="Buscar usuarios por nombre o email..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                            <th class="px6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-blue-600"></i>
                                        </div>
                                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="text-gray-500 text-sm">Sin roles asignados</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <!-- Botón Editar -->
                                        <button wire:click="editUser({{ $user->id }})"
                                                class="text-blue-600 hover:text-blue-800 px-3 py-1 rounded-lg hover:bg-blue-50 flex items-center gap-1"
                                                title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                            Editar
                                        </button>
                                        
                                        <!-- Botón Gestionar Roles -->
                                        <button wire:click="editUserRoles({{ $user->id }})"
                                                class="text-purple-600 hover:text-purple-800 px-3 py-1 rounded-lg hover:bg-purple-50 flex items-center gap-1"
                                                title="Gestionar roles">
                                            <i class="fas fa-user-cog"></i>
                                            Roles
                                        </button>
                                        
                                        <!-- Botón Eliminar -->
                                        @if($user->id !== auth()->id())
                                            <button wire:click="deleteUser({{ $user->id }})"
                                                    onclick="return confirm('¿Estás seguro de eliminar a {{ $user->name }}?')"
                                                    class="text-red-600 hover:text-red-800 px-3 py-1 rounded-lg hover:bg-red-50 flex items-center gap-1"
                                                    title="Eliminar usuario">
                                                <i class="fas fa-trash"></i>
                                                Eliminar
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay usuarios registrados</h3>
                <p class="text-gray-600">Comienza creando un nuevo usuario</p>
                <button wire:click="createUser" 
                        class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 mx-auto">
                    <i class="fas fa-user-plus"></i>
                    Crear Primer Usuario
                </button>
            </div>
        @endif
    </div>

    <!-- ========== MODAL PARA CREAR/EDITAR USUARIO ========== -->
    @if($showUserModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $isEditing ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}
                        </h3>
                        <button wire:click="closeUserModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Formulario -->
                    <form wire:submit.prevent="saveUser">
                        <!-- Nombre -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre Completo *
                            </label>
                            <input type="text" 
                                   wire:model="userForm.name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('userForm.name') border-red-500 @enderror"
                                   placeholder="Ej: Juan Pérez">
                            @error('userForm.name')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email *
                            </label>
                            <input type="email" 
                                   wire:model="userForm.email"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('userForm.email') border-red-500 @enderror"
                                   placeholder="ejemplo@dominio.com">
                            @error('userForm.email')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $isEditing ? 'Nueva Contraseña (dejar vacío para mantener la actual)' : 'Contraseña *' }}
                            </label>
                            <input type="password" 
                                   wire:model="userForm.password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('userForm.password') border-red-500 @enderror"
                                   placeholder="••••••••">
                            @error('userForm.password')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Confirmar Contraseña -->
                        @if(!$isEditing || !empty($userForm['password']))
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Confirmar Contraseña *
                            </label>
                            <input type="password" 
                                   wire:model="userForm.password_confirmation"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('userForm.password_confirmation') border-red-500 @enderror"
                                   placeholder="••••••••">
                            @error('userForm.password_confirmation')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        @endif

                        <!-- Rol -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Rol del Usuario *
                            </label>
                            <select wire:model="userForm.role_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('userForm.role_id') border-red-500 @enderror">
                                <option value="">Seleccione un rol</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('userForm.role_id')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3">
                            <button type="button" 
                                    wire:click="closeUserModal" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                                <i class="fas fa-save"></i>
                                {{ $isEditing ? 'Actualizar' : 'Crear Usuario' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ========== MODAL PARA GESTIONAR ROLES ========== -->
    @if($selectedUserId)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Asignar Roles</h3>
                        <button wire:click="cancelEdit" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <p class="text-gray-600 mb-6">
                        Selecciona los roles para: <span class="font-semibold">{{ $selectedUserName }}</span>
                    </p>

                    <!-- Roles Selection -->
                    <div class="space-y-3 mb-6 max-h-60 overflow-y-auto p-2">
                        @foreach($roles as $role)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="role_{{ $role->id }}"
                                       wire:model="selectedRoles"
                                       value="{{ $role->id }}"
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="role_{{ $role->id }}" class="ml-2 text-sm text-gray-700">
                                    {{ $role->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3">
                        <button wire:click="cancelEdit" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button wire:click="updateUserRoles" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Script para notificaciones -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('notify', (event) => {
            // Puedes usar Toastr, SweetAlert o el sistema de notificaciones que prefieras
            alert(event.message);
        });
    });
</script>