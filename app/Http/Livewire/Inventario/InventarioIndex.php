<?php

namespace App\Http\Livewire\Inventario;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\ProductoLote;
use App\Models\ProductoPresentaciones;
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

    protected $listeners = ['confirmacion' => 'confirmacion', 'refreshComponent' => '$refresh'];

    public function verificar($r){

        $presentaciones = ProductoPresentaciones::where('producto_id',$r->id)->get();

        $caja = 0;
        $unidades = 0;
        
        foreach($presentaciones as $presentacion){

            if($presentacion->nombre == 'caja') $caja++;
            if($presentacion->nombre == 'unidad') $unidades++;
             if($presentacion->nombre == 'kg') return 'kg';
        }

        if($caja==1 && $unidades==1) return 'caja';
        if($caja==0 && $unidades=1) return 'unidades';
    }

    public function cantcajas($r){

        $presentaciones = ProductoPresentaciones::where('producto_id',$r->id)->get();

        foreach($presentaciones as $presentacion){

            if($presentacion->nombre == 'caja') return $presentacion->cantidad_de_cajas;
        }

    }

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

     public function precio_present($registro,$factor){

        if($factor == 'unidades'){

            $present = ProductoPresentaciones::where('producto_id',$registro->id)->where('nombre','unidad')->first();

            $precio_bs_unidad = $present->precio_usd * Tasa::find(1)->tasa_actual;

            return [
                'precio_uni' => number_format($present->precio_usd, 2, '.', ''),
                'bs_uni' => number_format($precio_bs_unidad, 2, '.', ''),
            ];

        }

         if($factor == 'caja'){

       
            $present_unidades = ProductoPresentaciones::where('producto_id',$registro->id)->where('nombre','unidad')->first();
            $present_cajas = ProductoPresentaciones::where('producto_id',$registro->id)->where('nombre','caja')->first();

            $precio_bs_unidad = $present_unidades->precio_usd * Tasa::find(1)->tasa_actual;

            $precio_bs_caja = $present_cajas->precio_usd * Tasa::find(1)->tasa_actual;



            return [
                'unidad' => number_format($present_unidades->precio_usd, 2, '.', ''),
                'caja'   => number_format($present_cajas->precio_usd, 2, '.', ''),
                'bs_unidad' => number_format($precio_bs_unidad, 2, '.', ''),
                'bs_caja' => number_format($precio_bs_caja, 2, '.', ''),

            ];

            //return 'Unidades:'. number_format($present_unidades->precio_usd, 2, '.', '').'$'. 'Cajas:'. number_format($present_cajas->precio_usd, 2, '.', '').'$';
        }

        if($factor == 'kg'){

            $present = ProductoPresentaciones::where('producto_id',$registro->id)->where('nombre','kg')->first();

            $precio_bs_kg = $present->precio_usd * Tasa::find(1)->tasa_actual;

            return [
                'precio_kg' => number_format($present->precio_usd, 2, '.', ''),
                'bs_kg' => number_format($precio_bs_kg, 2, '.', ''),
            ];

        }




    }

    public function preciodol(){

    }

     public function delete($productoId){
        $this->product_delete = $productoId;
        $busqueda = ProductoVenta::where('producto_id',$productoId)->first();


        if($busqueda) $this->emit('errorSize', 'Este producto esta asociado a una venta, no puede eliminarlo');
        else $this->emit('confirm', 'Esta seguro de eliminar este producto?','inventario.inventario-index','confirmacion','El producto se ha eliminado.');
    }

    public function confirmacion(){

        $delete_presentacion=ProductoPresentaciones::where('producto_id',$this->product_delete)->get();

        foreach($delete_presentacion as $dp){
            $dp->delete();
        }

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
