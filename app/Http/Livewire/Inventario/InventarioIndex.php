<?php

namespace App\Http\Livewire\Inventario;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\ProductoLote;
use App\Models\ProductoVenta;
use Livewire\WithPagination;
use App\Models\Tasa;
use App\Models\Venta;
use Livewire\Component;

class InventarioIndex extends Component
{
     use WithPagination;
    
    public $perPage = 10;

    public $search,$product_delete;

    protected $listeners = ['render','confirmacion' => 'confirmacion'];

    public function render()
    {

        $registros = Producto::where('estado', 'Activo')
                ->where(function($query) {
                    $query->where('nombre', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('cod_barra', 'LIKE', '%' . $this->search . '%');
                })
                ->latest('id')
                ->paginate($this->perPage);



        return view('livewire.inventario.inventario-index',compact('registros'));
    }

    public function total_venta_bs($precio){

        $precio_bs = $precio * Tasa::find(1)->tasa_actual;

        return number_format($precio_bs, 2, '.', '');


    }

     public function delete($productoId){
        $this->product_delete = $productoId;
        $busqueda = ProductoVenta::where('producto_id',$productoId)->first();


        if($busqueda) $this->emit('errorSize', 'Este producto esta asociado a una venta, no puede eliminarlo');
        else $this->emit('confirm', 'Esta seguro de eliminar este producto?','inventario.inventario-index','confirmacion','El producto se ha eliminado.');
    }

    public function confirmacion(){
        $prod_destroy = Producto::where('id',$this->product_delete)->first();
        $prod_destroy->delete();

        $product_delete_lotes = ProductoLote::where('producto_id',$this->product_delete)->get();

        foreach($product_delete_lotes as $pl){
            $pl->delete();
        }


        $product_delete_compras = Compra::where('producto_id',$this->product_delete)->get();

        foreach($product_delete_compras as $pc){
            $pc->delete();
        }

    }
}
