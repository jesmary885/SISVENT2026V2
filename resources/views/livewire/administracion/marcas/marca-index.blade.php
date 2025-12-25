<div class="w-full p-4 bg-gradient-to-br bg-gray-200 ">
    <!-- Header Mejorado -->


    <!-- Barra de Búsqueda Mejorada -->
    <div class=" mx-auto mb-8">
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-200">
            <div class="flex flex-col lg:flex-row gap-4 items-center">
                <!-- Buscador -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input 
                        wire:model="search"
                        type="text" 
                        class="w-full pl-10 pr-4 py-4 bg-gray-100 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg transition-all duration-300 placeholder-gray-500 shadow-sm"
                        placeholder="Buscar marca por nombre..."
                    />
                </div>
                
                <!-- Botón Agregar -->
                <div class="w-full lg:w-auto">
                    @livewire('administracion.marcas.marca-create', ['tipo' => 'agregar'])
                </div>
            </div>
        </div>
    </div>

    @if($registros->count())
    <!-- Tabla Mejorada -->
    <div class=" mx-auto">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            <!-- Header de la tabla -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-list text-white text-xl mr-3"></i>
                        <h2 class="text-xl font-bold text-white">Marcas Registradas</h2>
                    </div>
                    <span class="bg-white text-blue-600 px-3 py-1 rounded-full text-sm font-bold">
                        {{ $registros->count() }} marcas
                    </span>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold text-gray-700">ID</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-700">nombre</th>

                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($registros as $registro)
                        <tr class="hover:bg-blue-50 transition-colors duration-200 group">

                              <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-box text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 group-hover:text-blue-600"> {{ $registro->id }}</p>
                                    </div>
                                </div>
                            </td>
                            
                                  <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg font-medium">
                                    {{ $registro->nombre }}
                                </span>
                            </td>

                            
                            <!-- Acciones -->
                            <td class="px-6 py-4">
                                <div class="flex justify-center space-x-2">
                            
                                    
                                    @livewire('administracion.marcas.marca-create', ['registro' => $registro,'tipo' => 'editar'], key('edit-'.$registro->id))
                                    

                                    
                                    <button
                                        wire:click="delete('{{$registro->id}}')"
                                        class="w-10 h-10 bg-red-500 cursor-pointer hover:bg-red-600 text-white rounded-lg flex items-center justify-center transition-all duration-300 transform hover:scale-110"
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <!-- Estado vacío mejorado -->
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl p-12 text-center border border-gray-200">
            <div class="w-24 h-24 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-box-open text-gray-500 text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">Inventario Vacío</h3>
            <p class="text-gray-600 mb-6 text-lg">No hay marcas registrados en el sistema. Comienza agregando tu primera marca.</p>
            <div class="flex justify-center">
                @livewire('administracion.marcas.marca-create', ['tipo' => 'agregar'])
            </div>
        </div>
    </div>
    @endif
</div>