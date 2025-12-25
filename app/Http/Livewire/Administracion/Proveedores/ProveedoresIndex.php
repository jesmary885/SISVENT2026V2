<?php

namespace App\Http\Livewire\Administracion\Proveedores;

use App\Models\Compra;
use App\Models\Proveedor;
use Livewire\Component;

class ProveedoresIndex extends Component
{

     public $search,$proveedor_delete;

    protected $listeners = ['render','confirmacion' => 'confirmacion'];

    public function render()
    {

         $registros = Proveedor::where('nombre_proveedor', 'LIKE', '%' . $this->search . '%')
                ->latest('id')
                ->paginate(20);

        return view('livewire.administracion.proveedores.proveedores-index',compact('registros'));
    }

    public function delete($proveedorId){
        $this->proveedor_delete = $proveedorId;
        $busqueda = Compra::where('proveedor_id',$proveedorId)->first();


        if($busqueda) $this->emit('errorSize', 'Este proveedor esta asociado a una compra, no puede eliminarlo');
        else $this->emit('confirm', 'Esta seguro de eliminar este proveedor?','administracion.proveedores.proveedores-index','confirmacion','El proveedor se ha eliminado.');
    }

    public function confirmacion(){
        $provee_destroy = Proveedor::where('id',$this->proveedor_delete)->first();
        $provee_destroy->delete();

       

    }
}
