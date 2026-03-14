<?php

namespace App\Http\Livewire\Administracion\Compras;

use App\Models\Producto;
use App\Models\ProductoPresentaciones;
use App\Models\Proveedor;
use App\Models\Tasa;
use Livewire\Component;

class ComprasEdit extends Component
{

    protected $listeners = ['render'];
    public $open = false,$compra, $tasa_actual, $registro,$user_id,$proveedores,$proveedor_id,$cantidad,$precio_compra, $tasa_compra,$fecha_vencimiento,$lote_numero,$metodo_pago,$total_dolares;

    protected $rules = [
      'cantidad' => 'required',
      'proveedor_id' => 'required',
      'metodo_pago' => 'required',
      'precio_compra' => 'required',
    ];




    public function close(){

        $this->open = false;

    }

    public function render()
    {
        return view('livewire.administracion.compras.compras-edit');
    }

     public function mount(){


        $this->cantidad = $this->registro->cantidad;

         $this->metodo_pago = $this->registro->metodo_pago;

         $this->precio_compra = $this->registro->precio_unitario;

        $this->tasa_actual=Tasa::find(1)->tasa_actual;



  
         $this->proveedores = Proveedor::all();
          $this->proveedor_id = $this->registro->proveedor_id;
      

        
    }


    public function save(){

      $rules = $this->rules;
      $this->validate($rules);

      if($this->registro->cantidad != $this->cantidad){
        if($this->registro->cantidad > $this->cantidad){

            $diferencia_cantidad = $this->registro->cantidad - $this->cantidad;


            $presentacion_caja = ProductoPresentaciones::where('producto_id',$this->registro->producto_id)
              ->where('nombre','caja')
              ->first();

            if($presentacion_caja){

              $cant_cajas_nueva =  $presentacion_caja->cantidad_de_cajas - $diferencia_cantidad;

              $presentacion_caja->update([
                'cantidad_de_cajas' => (float) $cant_cajas_nueva,
              ]);

              $stock_base_nueva = (float) $diferencia_cantidad * (float) $presentacion_caja->factor_base;
          
              $producto_modif = Producto::find($this->registro->producto_id);
              $product_cantidad_unidad = $producto_modif->stock_base - ($stock_base_nueva);

              $producto_modif->update([
                'stock_base' => $product_cantidad_unidad
              ]);

            }

            else{
              $producto_modif = Producto::find($this->registro->producto_id);
              $product_cantidad_unidad = $producto_modif->stock_base - ($this->cantidad );

              $producto_modif->update([
                'stock_base' => $product_cantidad_unidad
              ]);
            }

        } 

        else{

            $diferencia_cantidad = $this->cantidad - $this->registro->cantidad ;

            $presentacion_caja = ProductoPresentaciones::where('producto_id',$this->registro->producto_id)
              ->where('nombre','caja')
              ->first();

            if($presentacion_caja){

              $cant_cajas_nueva =  $presentacion_caja->cantidad_de_cajas + $diferencia_cantidad;

              $presentacion_caja->update([
                'cantidad_de_cajas' => (float) $cant_cajas_nueva,
              ]);

              $stock_base_nueva = (float) $diferencia_cantidad * (float) $presentacion_caja->factor_base;
          
              $producto_modif = Producto::find($this->registro->producto_id);
              $product_cantidad_unidad = $producto_modif->stock_base + ($stock_base_nueva);

              $producto_modif->update([
                'stock_base' => $product_cantidad_unidad
              ]);

            }

            else{
              $producto_modif = Producto::find($this->registro->producto_id);
              $product_cantidad_unidad = $producto_modif->stock_base + ($this->cantidad );

              $producto_modif->update([
                 'stock_base' => $product_cantidad_unidad
              ]);
            }
        }







        










      }

      
        $totalOriginal = 0;
        $totalUsd = 0;
        $moneda = '';
        $precioUnitario = 0;

    if(in_array($this->metodo_pago, ['bs_efec','pago_movil'])){

        $moneda = 'VES';
        $precioUnitario = $this->precio_compra;
        $totalOriginal = $precioUnitario * $this->cantidad;
        $totalUsd = $totalOriginal / $this->tasa_actual;

    }else{

        $moneda = 'USD';
        $precioUnitario = $this->precio_compra;
        $totalOriginal = $precioUnitario * $this->cantidad;
        $totalUsd = $totalOriginal;
    }


        $this->registro->update([
          'proveedor_id' => $this->proveedor_id,
          'cantidad' => $this->cantidad,
          'moneda_compra' => $moneda,
          'metodo_pago' => $this->metodo_pago,
          'precio_unitario' => $precioUnitario,
          'total_original' => $totalOriginal,
          'tasa_cambio_compra' => $this->tasa_actual,
          'total_usd_equivalente' => $totalUsd,
        ]);

       
          $this->reset(['open']);
          $this->emitTo('administracion.compras.compras-index','render');
  

        notyf()
          ->duration(9000) // 2 seconds
          ->position('y', 'top')
          ->position('x', 'right')
          ->addSuccess('compra modificada exitosamente');

    }
}
