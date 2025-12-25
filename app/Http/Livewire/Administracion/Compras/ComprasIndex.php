<?php

namespace App\Http\Livewire\Administracion\Compras;

use App\Models\Compra;
use Livewire\Component;

class ComprasIndex extends Component
{

     protected $listeners = ['render'];

    public function cant_productos($registro){

        return Compra::where('id',$registro->id)
        ->sum('cantidad');
    }

    public function subtotal_dol($registro){

       return Compra::where('id',$registro->id)->sum('precio_dolares');
    }

     public function subtotal_bol($registro){

       return Compra::where('id',$registro->id)->sum('precio_bolivares');
    }

    public function render()
    {

        $registros = Compra::latest('id')
                ->paginate(20);

        return view('livewire.administracion.compras.compras-index', compact('registros'));
    }
}
