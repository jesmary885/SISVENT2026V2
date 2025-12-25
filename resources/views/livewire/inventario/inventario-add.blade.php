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
      
    </style>


    <button type="button" wire:click="$set('open',true)" type="button" wire:loading.attr="disabled" class=" cursor-pointer action-btn bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg" title="Agregar stock">

        <i class="fas fa-plus text-sm"></i>
    
    </button>

    <x-dialog-modal wire:model="open" maxWidth="xl">

        <x-slot name="title">
            <div class=" flex justify-between ">

                <p>Agregar registro</p>

                <button type="button" wire:click="close" wire:loading.attr="disabled"  class=" cursor-pointer  py-2.5 px-3 me-2 mb-2 text-sm font-bold text-white focus:outline-none bg-black rounded-full border border-gray-200 hover:bg-gray-100 hover:text-gray-200 focus:z-10 focus:ring-4 focus:ring-gray-100  ">
                    X
                </button>






            </div>
            
        </x-slot>

        <x-slot name="content">

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

                            @error('cantidad')
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

{{--         
        @push('js')
            <script>
                function datePicker() {
                    return {
                        init() {
                            // Inicializar Flatpickr cuando el componente esté montado
                            this.$nextTick(() => {
                                flatpickr(this.$refs.dateInput, {
                                    dateFormat: "Y-m-d",
                                    locale: "es",
                                    minDate: "today",
                                    clickOpens: true, // Abrir al hacer clic en cualquier parte
                                    allowInput: false // No permitir entrada manual
                                });
                            });
                        }
                    }
                }
            </script>

        @endpush --}}

    </x-dialog-modal>
  

</div>