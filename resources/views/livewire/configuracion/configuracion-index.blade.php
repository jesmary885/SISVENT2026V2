<div>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
            background-color: white;
        }
        
        /* Estilo para el switch personalizado */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #3B82F6;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
    
    <div class="bg-gray-50 p-4 md:p-6">
        <div class="mx-auto">
            <!-- Encabezado -->
            <!-- Formulario principal -->
            <div class="card">
                <div class="p-6">
                    <!-- Sección: Documento de registro, nombre y dirección -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4">
                            <i class="far fa-address-card text-blue-500 text-lg mr-2"></i>
                            <h2 class="text-lg font-medium text-gray-700">Documento de registro, nombre y dirección</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <!-- Tipo de documento -->
                            <div>
                                <label for="tipo_documento" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tipo de documento
                                </label>
                                <select id="tipo_documento" wire:model.defer="tipo_documento" 
                                        class="w-full bg-gray-50 border border-gray-300 text-gray-700 py-2 px-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                    <option value="" selected disabled>Tipo de documento</option>
                                    <option value="Rif">Rif</option>
                                    <option value="Cedula">Cedula</option>
                                    <option value="Licencia">Licencia</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                    <option value="DNI">DNI</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                @error('tipo_documento')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Número de documento -->
                            <div>
                                <label for="documento" class="block text-sm font-medium text-gray-700 mb-1">
                                    Número de documento
                                </label>
                                <input id="documento" wire:model.defer="documento" type="text" 
                                       class="w-full bg-gray-50 border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                       placeholder="Número de documento">
                                @error('documento')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nombre de la empresa -->
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre de la empresa
                                </label>
                                <input id="nombre" wire:model.defer="nombre" type="text" 
                                       class="w-full bg-gray-50 border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                       placeholder="Nombre de la empresa">
                                @error('nombre')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Dirección -->
                        <div>
                            <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">
                                Dirección de la empresa
                            </label>
                            <input id="direccion" wire:model.defer="direccion" type="text" 
                                   class="w-full bg-gray-50 border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                   placeholder="Dirección de la empresa">
                            @error('direccion')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <!-- NUEVA SECCIÓN: Configuración de Impuestos/IVA -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-percentage text-blue-500 text-lg mr-2"></i>
                            <h2 class="text-lg font-medium text-gray-700">Configuración de Impuestos</h2>
                        </div>

                        <!-- Activar/Desactivar IVA -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="font-medium text-gray-800">Facturar con Impuesto</h3>
                                    <p class="text-sm text-gray-600">
                                        Active esta opción si su negocio debe facturar con impuestos.
                                    </p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" wire:model="facturar_con_iva">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            @if($facturar_con_iva)
                            <div class="mt-4 p-4 bg-white rounded-lg border border-blue-100">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Nombre del Impuesto -->
                                    <div>
                                        <label for="nombre_impuesto" class="block text-sm font-medium text-gray-700 mb-1">
                                            Nombre del Impuesto
                                        </label>
                                        <input id="nombre_impuesto" wire:model.defer="nombre_impuesto" type="text" 
                                               class="w-full bg-gray-50 border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                               placeholder="Ej: IVA, ISV, Impuesto"
                                               value="{{ $nombre_impuesto }}">
                                        <p class="text-xs text-gray-500 mt-1">
                                            Nombre que aparecerá en las facturas (ej: IVA, ISV, GST)
                                        </p>
                                        @error('nombre_impuesto')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Porcentaje del Impuesto -->
                                    <div>
                                        <label for="porcentaje_iva" class="block text-sm font-medium text-gray-700 mb-1">
                                            Porcentaje del Impuesto (%)
                                        </label>
                                        <div class="relative">
                                            <input id="porcentaje_iva" wire:model.defer="porcentaje_iva" type="number" 
                                                   min="0" max="100" step="0.01"
                                                   class="w-full bg-gray-50 border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                                   placeholder="Ej: 16.00">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <span class="text-gray-500">%</span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Porcentaje a aplicar (ej: 16 para 16%)
                                        </p>
                                        @error('porcentaje_iva')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- Información adicional -->
                                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <div>
                                            <p class="text-sm text-blue-700 font-medium">Nota importante:</p>
                                            <ul class="text-xs text-blue-600 mt-1 ml-4 list-disc">
                                                <li>Los productos pueden configurarse individualmente como exentos o no exentos.</li>
                                                <li>Al activar esta opción, se calculará automáticamente el impuesto en cada factura.</li>
                                                <li>Los montos de impuesto se registrarán separadamente en los reportes.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="mt-4 p-4 bg-gray-100 rounded-lg text-center">
                                <i class="fas fa-ban text-gray-400 text-2xl mb-2"></i>
                                <p class="text-gray-600 text-sm">
                                    El sistema facturará <span class="font-medium">SIN IMPUESTOS</span>. 
                                    Active la opción superior para habilitar el cálculo de impuestos.
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <!-- Sección: Logo del negocio -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4">
                            <i class="far fa-image text-blue-500 text-lg mr-2"></i>
                            <h2 class="text-lg font-medium text-gray-700">Logo del negocio</h2>
                        </div>

                        <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                            <!-- Vista previa del logo -->
                            <div class="w-full md:w-1/3 flex justify-center">
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 w-48 h-48 flex items-center justify-center bg-gray-50">
                                    <div id="logo-preview" class="text-center">
                                        <i class="fas fa-image text-gray-400 text-4xl mb-2"></i>
                                        <p class="text-gray-500 text-sm">Vista previa del logo</p>
                                    </div>
                                    <img id="logo-img" src="" class="hidden max-w-full max-h-full object-contain">
                                </div>
                            </div>

                            <!-- Cargar logo -->
                            <div class="w-full md:w-2/3">
                                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Cargar logo
                                </label>
                                <input type="file" id="logo" wire:model.defer="logo" name="logo" 
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition duration-200" 
                                       accept="image/*">
                                <p class="text-xs text-gray-500 mt-2">Tamaño máximo 3MB. Resolución recomendada 300px X 300px o superior.</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <!-- Sección: Información de contacto -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-phone-volume text-blue-500 text-lg mr-2"></i>
                            <h2 class="text-lg font-medium text-gray-700">Información de contacto</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Teléfono -->
                            <div>
                                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">
                                    Teléfono de contacto
                                </label>
                                <input id="telefono" wire:model.defer="telefono" type="tel" 
                                       class="w-full bg-gray-50 border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                       placeholder="Teléfono de contacto">
                                @error('telefono')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email de la empresa
                                </label>
                                <input id="email" type="email" wire:model.defer="email" 
                                       class="w-full bg-gray-50 border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                                       placeholder="Email de la empresa">
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Botón de envío -->
                    <div class="flex justify-end mt-6">
                        <button wire:click="update()" type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg flex items-center transition duration-200 transform hover:-translate-y-0.5 shadow-md hover:shadow-lg">
                            <i class="fas fa-file-download mr-2"></i> 
                            Actualizar información
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Vista previa del logo
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const logoPreview = document.getElementById('logo-preview');
                    const logoImg = document.getElementById('logo-img');
                    
                    logoPreview.classList.add('hidden');
                    logoImg.src = e.target.result;
                    logoImg.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        // Efectos de hover en los campos de entrada
        document.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.classList.add('shadow-md');
            });
            
            element.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-md');
            });
        });

        // Mostrar/ocultar campos de impuesto dinámicamente
        Livewire.on('impuestoActivado', function(activado) {
            const camposImpuesto = document.querySelectorAll('.campo-impuesto');
            camposImpuesto.forEach(campo => {
                if (activado) {
                    campo.classList.remove('opacity-50', 'pointer-events-none');
                } else {
                    campo.classList.add('opacity-50', 'pointer-events-none');
                }
            });
        });
    </script>
</div>