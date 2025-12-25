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


    <button type="button" wire:click="$set('open',true)" type="button" wire:loading.attr="disabled" class="cursor-pointer action-btn bg-purple-500 hover:bg-purple-600 text-white p-2 rounded-lg" title="Generar código de barras">

        <i class="fas fa-barcode text-sm"></i>
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
                                placeholder="Ejemplo 100"
                            >
                            @error('cantidad')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>




     

        </x-slot>

        <x-slot name="footer">


            
            <button 
                type="button" 
                wire:click="print"
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