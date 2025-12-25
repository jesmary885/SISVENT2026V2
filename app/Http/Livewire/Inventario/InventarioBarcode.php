<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;


class InventarioBarcode extends Component
{

    protected $listeners = ['render'];
    public $tipo,$registro,$open = false,$cantidad;



    protected $rules = [
        'cantidad' => 'required',
    ];

      public function close(){

        

        $this->open = false;

      //  $this->dispatch('volver');

    }
    
    public function render()
    {
        return view('livewire.inventario.inventario-barcode');
    }

    
    public function print(){

        $rules = $this->rules;
        $this->validate($rules);

        if($this->cantidad < 0){
            $this->emit('errorSize','Ha ingresado un valor negativo, intentelo de nuevo');
        }else{
            $data = [
                'cod_barra' => $this->registro->cod_barra,
                'nombre' => $this->registro->nombre,
                'cantidad' => $this->cantidad,     
            ];
    
            $pdf = Pdf::loadView('inventario.barcode',$data)->output();
    
            return response()->streamDownload(
                fn () => print($pdf),
               "filename.pdf"
                );
        }

       


    }
}
