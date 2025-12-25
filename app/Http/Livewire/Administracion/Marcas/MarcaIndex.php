<?php

namespace App\Http\Livewire\Administracion\Marcas;

use App\Models\Marca;
use App\Models\Producto;
use Livewire\Component;

class MarcaIndex extends Component
{

     public $search,$marca_delete;

    protected $listeners = ['render','confirmacion' => 'confirmacion'];

    public function render()
    {

          $registros = Marca::where('nombre', 'LIKE', '%' . $this->search . '%')
                ->latest('id')
                ->paginate(20);


        return view('livewire.administracion.marcas.marca-index',compact('registros'));
    }

    public function delete($marcaId){
        $this->marca_delete = $marcaId;
        $busqueda = Producto::where('marca_id',$marcaId)->first();


        if($busqueda) $this->emit('errorSize', 'Esta marca esta asociada a un producto, no puede eliminarla');
        else $this->emit('confirm', 'Esta seguro de eliminar esta marca?','administracion.marcas.marca-index','confirmacion','La marca se ha eliminado.');
    }

    public function confirmacion(){
        $marc_destroy = Marca::where('id',$this->marca_delete)->first();
        $marc_destroy->delete();

       

    }

}
