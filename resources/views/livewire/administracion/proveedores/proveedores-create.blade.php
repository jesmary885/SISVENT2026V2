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

    @if($tipo == 'agregar')

        <button type="button" wire:click="$set('open',true)" type="button" wire:loading.attr="disabled" class="cursor-pointer update-btn w-full p-4 text-white font-bold text-lg rounded-xl flex items-center justify-center">
            <i class="fas fa-plus-circle mr-2"></i>
            Agregar Proveedor
        </button>

    @else

     <button type="button" wire:click="$set('open',true)" type="button" wire:loading.attr="disabled" class=" cursor-pointer action-btn bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg">
           <i class="fas fa-edit text-sm"></i>
        
        </button>

    @endif


                    



    <x-dialog-modal wire:model="open" maxWidth="4xl">

        <x-slot name="title">
            <div class=" flex justify-between ">

                @if($tipo == 'agregar')

                <p>Agregar registro</p>

                @else

                <p>Editar registro</p>
                @endif

               <button type="button" wire:click="close" wire:loading.attr="disabled"  class=" cursor-pointer  py-2.5 px-3 me-2 mb-2 text-sm font-bold text-white focus:outline-none bg-black rounded-full border border-gray-200 hover:bg-gray-100 hover:text-gray-200 focus:z-10 focus:ring-4 focus:ring-gray-100  ">
                    X
                </button>





            </div>
            
        </x-slot>

        <x-slot name="content">

            <div class="mt-2 w-full h-full ">


                <div class=" flex justify-between p-4 ">
                    <div class="w-full mr-2">
                        <div>
                            <label for="nombre_encargado" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del encargado
                            </label>
                            <input 
                                type="text" 
                                id="nombre_encargado"
                                wire:model.defer="nombre_encargado"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white placeholder-gray-400"
                                placeholder="Ejemplo. Maria Perez"
                            >
                            @error('nombre_encargado')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="w-full">
                        <div>
                            <label for="nombre_proveedor" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del proveedor
                            </label>
                            <input 
                                type="text" 
                                id="nombre_proveedor"
                                wire:model.defer="nombre_proveedor"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white placeholder-gray-400"
                                placeholder="Ejemplo. Inversiones xx"
                            >
                            @error('nombre_proveedor')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

           

                <div class=" flex justify-between p-4">

                    <div class="w-1/2 mr-2">

                        <label for="tipo_documento" class="block text-sm font-medium text-gray-700 mb-2 ">
                            Tipo de documento
                        </label>

                     

                            <select 
                                id="tipo_documento"
                                wire:model="tipo_documento"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white appearance-none"
                            >
                                <option value="" selected>Seleccione una opción</option>
                                <option value="Rif">Rif</option>
                                <option value="Cedula">Cédula</option>

                            </select>

                            @error('tipo_documento')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                    </div>

                    <div class="w-1/2">
                        <div>
                            <label for="nombre_encargado" class="block text-sm font-medium text-gray-700 mb-2">
                                Documento Nro
                            </label>
                            <input 
                                type="text" 
                                id="nro_documento"
                                wire:model.defer="nro_documento"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white placeholder-gray-400"
                                placeholder="Ejemplo. J-0000000-1"
                            >
                            @error('nro_documento')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                  

                



                </div>

                <div class=" flex justify-between p-4 ">
                    <div class="w-full mr-2">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </label>
                            <input 
                                type="email" 
                                id="email"
                                wire:model.defer="email"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white placeholder-gray-400"
                                placeholder="Ejemplo. inversionesxx@inversiones.com"
                            >
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="w-full">
                        <div>
                            <label for="nombre_proveedor" class="block text-sm font-medium text-gray-700 mb-2">
                                Teléfono
                            </label>
                            <input 
                                type="number" 
                                id="telefono"
                                wire:model.defer="telefono"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white placeholder-gray-400"
                                placeholder="Ejemplo. 0414-0000000"
                            >
                            @error('telefono')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class=" flex justify-between p-4 ">
                    <div class="w-full mr-2">
                        <div>
                            <label for="direccion" class="block text-sm font-medium text-gray-700 mb-2">
                                Dirección
                            </label>
                            <input 
                                type="direccion" 
                                id="direccion"
                                wire:model.defer="direccion"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white placeholder-gray-400"
                                placeholder="Ejemplo. Calle xxxx. Ciudad xxxx"
                            >
                            @error('direccion')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

          
                </div>

        

         
            </div>

        </x-slot>

        <x-slot name="footer">


            <button 
                type="button" 
                wire:click="save"
                wire:loading.attr="disabled"
                
                 class="bg-blue-600 cursor-pointer hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg flex items-center transition duration-200 transform hover:-translate-y-0.5 shadow-md hover:shadow-lg">
                
       
                        <span wire:loading>Procesando...</span>
                 Guardar
            </button>




            <button type="button" wire:click="close" wire:loading.attr="disabled" class="bg-blue-600 cursor-pointer hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg flex items-center transition duration-200 transform hover:-translate-y-0.5 shadow-md hover:shadow-lg ml-2">
                Cerrar
            </button>



      

        </x-slot>



    </x-dialog-modal>



  

</div>