<div class=" font-Arima" >

    <style>
      
        .icon::after{
        content: '';
        display: block;
        position: absolute;
        border-top: 17px solid transparent;
        border-bottom: 15px solid transparent;
        border-left: 14px solid rgb(37 99 235 / var(--tw-bg-opacity));
        left: 100%;
        top: 0;
      }
      
      input[type="file"] {
              display: none;
          }
      
          .custom-file-upload {
          border: 1px solid #ccc;
         
          padding: 6px 12px;
          cursor: pointer;
          }


        select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

         .modal-fix {
            max-height: 85vh !important;
            overflow-y: auto !important;
        }

        /* Asegurar que el modal esté por encima de todo */
        [x-dialog-modal] {
            z-index: 10000 !important;
        }

        /* Forzar que el contenido del modal sea visible */
        [x-slot="content"] {
            max-height: 60vh !important;
            overflow-y: auto !important;
        }

         .update-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .update-btn:active {
            transform: translateY(0);
        }
        
        .update-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .update-btn:hover::after {
            left: 100%;
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }


        
        .last-update {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
 
      
    </style>


        <button type="button" wire:click="$set('open',true)" wire:loading.attr="disabled"
                    class="bg-green-600 hover:bg-green-700 cursor-pointer p-4 text-white font-bold text-lg rounded-xl transition duration-200 flex items-center space-x-2">
                <i class="fas fa-file-pdf"></i>
                <span wire:loading.remove>Exportar / Importar</span>
                <span wire:loading>
                    <i class="fas fa-spinner fa-spin"></i>
                    Generando...
                </span>
        </button>




    <x-dialog-modal wire:model="open" maxWidth="4xl">

        <x-slot name="title">


            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-file-export text-green-500 mr-2"></i>
                        Importar / Exportar Inventario
                    </h2>
                </div>

                <!-- Script para notificaciones -->
                <script>
                    document.addEventListener('livewire:initialized', () => {
                        Livewire.on('notify', (event) => {
                            // Puedes usar Toast, SweetAlert, o alert nativo
                            alert(event.message);
                        });
                    });
                </script>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Exportación -->
                    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-file-export text-blue-500 text-2xl mr-3"></i>
                            <h3 class="text-xl font-bold text-blue-800">Exportar Inventario</h3>
                        </div>
                        
                        <p class="text-blue-700 mb-4">
                            Descarga un archivo Excel con todo tu inventario actual.
                        </p>

                        <button wire:click="exportar" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-semibold transition duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-download"></i>
                            <span>Exportar a Excel</span>
                        </button>

                        <div class="mt-4 text-sm text-blue-600">
                            <p class="font-semibold">El archivo incluirá:</p>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>Todos los productos del inventario</li>
                                <li>Stock actual y stock mínimo</li>
                                <li>Precios y categorías</li>
                                <li>Información de marcas</li>
                                <li>Fechas de creación y actualización</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Importación -->
                    <!-- Importación -->
<div class="bg-green-50 rounded-xl p-6 border border-green-200">
    <div class="flex items-center mb-4">
        <i class="fas fa-file-import text-green-500 text-2xl mr-3"></i>
        <h3 class="text-xl font-bold text-green-800">Importar Inventario</h3>
    </div>

    <p class="text-green-700 mb-4">
        Actualiza tu inventario desde un archivo Excel.
    </p>

    <div class="space-y-4">
        <!-- Descargar plantilla -->
        <button wire:click="descargarPlantilla" 
                class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg font-semibold transition duration-200 flex items-center justify-center space-x-2">
            <i class="fas fa-download"></i>
            <span>Descargar Plantilla</span>
        </button>

        <!-- Subir archivo - VERSIÓN CORREGIDA -->
        <div>
            <label class="block text-sm font-medium text-green-700 mb-2">
                Subir archivo Excel:
            </label>
            
            <div x-data="{ isUploading: false, progress: 0 }" 
                 x-on:livewire-upload-start="isUploading = true"
                 x-on:livewire-upload-finish="isUploading = false"
                 x-on:livewire-upload-error="isUploading = false"
                 x-on:livewire-upload-progress="progress = $event.detail.progress">
                
                <!-- Área de upload visible -->
                <label for="archivo_upload" class="cursor-pointer">
                    <div class="border-2 border-dashed border-green-300 rounded-lg p-6 text-center transition duration-200 hover:border-green-400 hover:bg-green-100">
                        <template x-if="!isUploading && !$wire.archivo">
                            <div class="space-y-3">
                                <i class="fas fa-cloud-upload-alt text-green-400 text-3xl"></i>
                                <div>
                                    <p class="text-green-600 font-medium">Haz clic para seleccionar un archivo</p>
                                    <p class="text-sm text-green-500 mt-1">o arrastra y suelta aquí</p>
                                </div>
                                <p class="text-xs text-green-400">Formatos: .xlsx, .xls, .csv (Máx. 10MB)</p>
                            </div>
                        </template>

                        <template x-if="isUploading">
                            <div class="space-y-3">
                                <i class="fas fa-spinner fa-spin text-green-500 text-2xl"></i>
                                <p class="text-green-600 font-medium">Subiendo archivo...</p>
                                <div class="w-full bg-green-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" 
                                         :style="`width: ${progress}%`"></div>
                                </div>
                                <p class="text-xs text-green-500" x-text="`${progress}% completado`"></p>
                            </div>
                        </template>

                        <template x-if="!isUploading && $wire.archivo">
                            <div class="space-y-3">
                                <i class="fas fa-file-excel text-green-500 text-2xl"></i>
                                <div>
                                    <p class="text-green-700 font-medium" x-text="$wire.nombreArchivo"></p>
                                    <p class="text-sm text-green-600">Archivo listo para importar</p>
                                </div>
                                <button type="button" 
                                        wire:click="cancelarImportacion"
                                        class="text-red-500 hover:text-red-700 text-sm flex items-center justify-center space-x-1">
                                    <i class="fas fa-times"></i>
                                    <span>Eliminar archivo</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </label>

                <!-- Input file real (oculto pero funcional) -->
                <input type="file" 
                       id="archivo_upload"
                       wire:model="archivo" 
                       accept=".xlsx,.xls,.csv"
                       class="hidden">
                
                @error('archivo')
                    <div class="mt-2 text-red-600 text-sm flex items-center bg-red-50 p-2 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex space-x-2">
            @if($archivo)
                <button wire:click="cancelarImportacion" 
                        type="button"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 px-4 rounded-lg font-semibold transition duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Cancelar</span>
                </button>
            @endif
            
            <button wire:click="importar" 
                    wire:loading.attr="disabled"
                    wire:target="importar"
                    type="button"
                    class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white py-3 px-4 rounded-lg font-semibold transition duration-200 flex items-center justify-center space-x-2"
                    @if(!$archivo) disabled @endif>
                <i class="fas fa-upload"></i>
                <span wire:loading.remove wire:target="importar">Importar Inventario</span>
                <span wire:loading wire:target="importar">
                    <i class="fas fa-spinner fa-spin"></i>
                    Procesando...
                </span>
            </button>
        </div>
    </div>

    <div class="mt-4 text-sm text-green-600">
        <p class="font-semibold">Funcionalidades:</p>
        <ul class="list-disc list-inside mt-2 space-y-1">
            <li>Actualiza productos existentes</li>
            <li>Crea nuevos productos</li>
            <li>Gestiona automáticamente las marcas</li>
            <li>Validación de datos integrada</li>
            <li>Reporte de errores detallado</li>
        </ul>
    </div>
</div>
                </div>

                <!-- Mostrar errores de importación -->
                @if($mostrarErrores && count($errores) > 0)
                <div class="mt-6 bg-red-50 rounded-xl p-6 border border-red-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-red-800 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Errores en la Importación ({{ count($errores) }})
                        </h3>
                        <button wire:click="$set('mostrarErrores', false)" 
                                class="text-red-600 hover:text-red-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach($errores as $error)
                        <div class="bg-white rounded-lg p-4 border border-red-200">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-semibold text-red-700">Fila {{ $error['fila'] }}</span>
                            </div>
                            <div class="text-sm text-red-600">
                                @foreach($error['errores'] as $mensajeError)
                                    <p class="mb-1">• {{ $mensajeError }}</p>
                                @endforeach
                            </div>
                            @if(isset($error['datos']))
                            <div class="mt-2 text-xs text-gray-500">
                                <strong>Datos:</strong> {{ json_encode($error['datos']) }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Información de formato -->
                <div class="mt-6 bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Información sobre el Formato
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Campos requeridos:</h4>
                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                <li><strong>nombre</strong> - Nombre del producto (texto)</li>
                                <li><strong>precio_de_venta</strong> - Precio de venta (número)</li>
                                <li><strong>cantidad_en_stock</strong> - Stock actual (número entero)</li>
                                <li><strong>marca</strong> - Nombre de la marca (texto)</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Campos opcionales:</h4>
                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                <li><strong>codigo_de_barras</strong> - Código único (texto)</li>
                                <li><strong>presentacion</strong> - Presentación (texto)</li>
                                <li><strong>categoria</strong> - Categoría (texto)</li>
                                <li><strong>stock_minimo</strong> - Stock mínimo (número, default: 5)</li>
                                <li><strong>exento</strong> - "Si" o "No" (default: "Si")</li>
                                <li><strong>estado</strong> - Estado del producto (texto)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                        <h4 class="font-semibold text-yellow-800 mb-2">💡 Consejos para importar:</h4>
                        <ul class="list-disc list-inside space-y-1 text-yellow-700 text-sm">
                            <li>Usa la plantilla como referencia</li>
                            <li>Mantén los nombres de las columnas exactos</li>
                            <li>Los productos existentes se actualizarán si coinciden por nombre o código de barras</li>
                            <li>Las marcas nuevas se crearán automáticamente</li>
                        </ul>
                    </div>
                </div>
            </div>

         </x-slot>

        <x-slot name="content">

            <div class="mt-2 w-full h-full ">




        

         
            </div>

        </x-slot>

        <x-slot name="footer">


  

            <button type="button" wire:click="close" wire:loading.attr="disabled" class="bg-blue-600 cursor-pointer hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg flex items-center transition duration-200 transform hover:-translate-y-0.5 shadow-md hover:shadow-lg ml-2">
                Cerrar
            </button>



      

        </x-slot>



    </x-dialog-modal>


</div>