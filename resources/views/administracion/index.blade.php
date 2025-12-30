<x-app-layout>
    <div class="font-sans">
        <div x-data="{ openTab: 1 }" class=" bg-gradient-to-br from-gray-50 to-blue-50 ">
            <div class=" mx-auto ">
                <!-- Pestañas CORREGIDAS -->
                <div class="mb-2">
                    <div class="bg-white rounded-2xl p-2 shadow-xl border border-gray-200">
                        <div class="flex space-x-2">
                            <button 
                                x-on:click="openTab = 1" 
                                :class="{ 
                                    'bg-blue-600 text-white': openTab === 1,  <!-- FONDO AZUL cuando está activo -->
                                    'bg-gray-200 text-gray-700 hover:bg-gray-300': openTab !== 1  <!-- FONDO GRIS cuando NO está activo -->
                                }" 
                                class="flex-1 cursor-pointer py-4 px-6 rounded-xl font-semibold transition-all duration-300 ease-in-out flex items-center justify-center space-x-2"
                            >
                                <i class="fas fa-exchange-alt text-sm"></i>
                                <span>Marcas de productos</span>
                            </button>

                            <button 
                                x-on:click="openTab = 2" 
                                :class="{ 
                                    'bg-blue-600 text-white': openTab === 2,  <!-- FONDO AZUL cuando está activo -->
                                    'bg-gray-200 text-gray-700 hover:bg-gray-300': openTab !== 2  <!-- FONDO GRIS cuando NO está activo -->
                                }" 
                                class="flex-1 cursor-pointer py-4 px-6 rounded-xl font-semibold transition-all duration-300 ease-in-out flex items-center justify-center space-x-2"
                            >
                                <i class="fas fa-store text-sm"></i>
                                <span>Proveedores</span>
                            </button>
                            
                            <button 
                                x-on:click="openTab = 3" 
                                :class="{ 
                                    'bg-blue-600 text-white': openTab === 3,  <!-- FONDO AZUL cuando está activo -->
                                    'bg-gray-200 text-gray-700 hover:bg-gray-300': openTab !== 3  <!-- FONDO GRIS cuando NO está activo -->
                                }" 
                                class="flex-1 cursor-pointer py-4 px-6 rounded-xl font-semibold transition-all duration-300 ease-in-out flex items-center justify-center space-x-2"
                            >
                                <i class="fas fa-store text-sm"></i>
                                <span>Compras</span>
                            </button>

                            <button 
                                x-on:click="openTab = 4" 
                                :class="{ 
                                    'bg-blue-600 text-white': openTab === 4,  <!-- FONDO AZUL cuando está activo -->
                                    'bg-gray-200 text-gray-700 hover:bg-gray-300': openTab !== 4  <!-- FONDO GRIS cuando NO está activo -->
                                }" 
                                class="flex-1 cursor-pointer py-4 px-6 rounded-xl font-semibold transition-all duration-300 ease-in-out flex items-center justify-center space-x-2"
                            >
                                <i class="fas fa-store text-sm"></i>
                                <span>Usuarios</span>
                            </button>

                            <button 
                                x-on:click="openTab = 5" 
                                :class="{ 
                                    'bg-blue-600 text-white': openTab === 5,  <!-- FONDO AZUL cuando está activo -->
                                    'bg-gray-200 text-gray-700 hover:bg-gray-300': openTab !== 5  <!-- FONDO GRIS cuando NO está activo -->
                                }" 
                                class="flex-1 cursor-pointer py-4 px-6 rounded-xl font-semibold transition-all duration-300 ease-in-out flex items-center justify-center space-x-2"
                            >
                                <i class="fas fa-store text-sm"></i>
                                <span>Roles y permisos</span>
                            </button>


                        </div>
                    </div>
                </div>

                <!-- Contenido -->
                <div x-show="openTab === 1" class="transition-all duration-300">
                    @livewire('administracion.marcas.marca-index') 
                </div>

                <div x-show="openTab === 2" class="transition-all duration-300">
                    @livewire('administracion.proveedores.proveedores-index') 
                </div>

                 <div x-show="openTab === 3" class="transition-all duration-300">
                    @livewire('administracion.compras.compras-index') 
                </div>

                <div x-show="openTab === 4" class="transition-all duration-300">
                    @livewire('user.user-role-manager') 
                </div>

                 <div x-show="openTab === 5" class="transition-all duration-300">
                    @livewire('roles.role-index') 
                </div>

            </div>
        </div>
    </div>
</x-app-layout>