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



     <button type="button" wire:click="$set('open',true)" type="button" wire:loading.attr="disabled" class=" cursor-pointer action-btn bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg">
           <i class="fas fa-edit text-sm"></i>
        
        </button>





    <x-dialog-modal wire:model="open" maxWidth="4xl">

        <x-slot name="title">
            <div class=" flex justify-between ">


                <p>Editar registro de compra del producto {{$registro->producto->nombre}}</p>
               
               <button type="button" wire:click="close" wire:loading.attr="disabled"  class=" cursor-pointer  py-2.5 px-3 me-2 mb-2 text-sm font-bold text-white focus:outline-none bg-black rounded-full border border-gray-200 hover:bg-gray-100 hover:text-gray-200 focus:z-10 focus:ring-4 focus:ring-gray-100  ">
                    X
                </button>





            </div>
            
        </x-slot>

        <x-slot name="content">

            <div class="mt-2 w-full h-full ">

                
            <div class=" flex  p-4">

                    <div class="w-1/2 mr-3">
                        <div>
                            <label for="cod_barra" class="block text-sm font-medium text-gray-700 mb-2">
                                Cantidad
                            </label>
                            <input 
                                type="number" 
                                id="cantidad"
                                wire:model.defer="cantidad"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white placeholder-gray-400"
                                placeholder="Ejemplo 500"
                            >
                            @error('cantidad')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="w-1/2 mr-3">
                        <div>
                            <label for="proveedor" class="block text-sm font-medium text-gray-700 mb-2">
                                Proveedor
                            </label>
                            <select wire:model.defer="proveedor_id" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white appearance-none">
                                <option value="" selected>Seleccione una opción</option>
                                @foreach ($proveedores as $proveedor)
                                    <option value="{{$proveedor->id}}">{{$proveedor->nombre_proveedor}}</option>
                                @endforeach
                            </select>

                            @error('proveedor_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>


            </div>

            
 

            <div class=" flex p-4">
                    <div class="w-1/2 mr-3">

                        <label for="metodo_pago" class="block text-sm font-medium text-gray-700 mb-2 ">
                            Método de pago
                        </label>

                     

                            <select 
                                id="metodo_pago"
                                wire:model="metodo_pago"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white appearance-none"
                            >
                                <option value="" selected>Seleccione una opción</option>
                                <option value="dol_efec">Dólares en efectivo</option>
                                <option value="bs_efec">Bolívares en efectivo</option>
                                <option value="pago_movil">Pago móvil</option>
                                <option value="debito">Débito</option>
                                <option value="usdt">USDT</option>
                        
                            </select>

                            @error('metodo_pago')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                    </div>


                    <div class="w-1/2">

                        @if($metodo_pago == 'bs_efec' || $metodo_pago == 'pago_movil' )
                            <div>
                                <label for="cod_barra" class="block text-sm font-medium text-gray-700 mb-2">
                                    Precio de compra en Bolívares
                                </label>
                                <input 
                                    type="number" 
                                    id="precio_compra_bolivares"
                                    wire:model.defer="precio_compra_bolivares"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white placeholder-gray-400"
                                    placeholder="Ejemplo 1000"
                                >
                                @error('precio_compra_bolivares')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                        @if($metodo_pago == 'dol_efec' || $metodo_pago == 'usdt' )

                            <div>
                                <label for="cod_barra" class="block text-sm font-medium text-gray-700 mb-2">
                                    Precio de compra en Dólares
                                </label>
                                <input 
                                    type="number" 
                                    id="precio_compra_dolares"
                                    wire:model.defer="precio_compra_dolares"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white placeholder-gray-400"
                                    placeholder="Ejemplo 10"
                                >
                                @error('precio_compra_dolares')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif


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