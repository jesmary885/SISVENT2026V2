<?php

namespace App\Http\Livewire\Administracion\Compras;

use App\Models\Proveedor;
use Livewire\Component;

class ComprasEdit extends Component
{

    protected $listeners = ['render'];
    public $open = false,$registro,$user_id,$proveedores,$proveedor_id,$cantidad,$precio_compra_dolares,$precio_compra_bolivares, $tasa_compra,$fecha_vencimiento,$lote_numero,$metodo_pago,$total_dolares;

    protected $rules = [
      'nombre' => 'required|max:255|min:2',
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


         $this->proveedores = Proveedor::all();
          $this->proveedor_id = $this->registro->proveedor_id;
      

        
    }


    public function save(){

      $rules = $this->rules;
      $this->validate($rules);


        $compra->producto_id = $this->registro->id;
        $compra->user_id= $this->user_id;
        $compra->caja_id= 1;
        $compra->proveedor_id= $this->proveedor_id;
        $compra->cantidad= $this->cantidad;
        $compra->metodo_pago= $this->metodo_pago;

       if($this->metodo_pago == 'bs_efec' || $this->metodo_pago == 'pago_movil' ){

        $this->registro->update([
            'producto_id' => $this->registro->id,
            'user_id' => $this->user_id,
            'caja_id' => 1,
            'proveedor_id' => $this->proveedor_id,
            'cantidad' => $this->cantidad,
            'metodo_pago' => $this->metodo_pago,
            'precio_compra_bolivares' => $this->precio_compra_bolivares,
            'total_pagado_bolivares' => $this->precio_compra_bolivares * $this->cantidad,
            
        ]);

       

        }else{

             $this->registro->update([
                'producto_id' => $this->registro->id,
                'user_id' => $this->user_id,
                'caja_id' => 1,
                'proveedor_id' => $this->proveedor_id,
                'cantidad' => $this->cantidad,
                'metodo_pago' => $this->metodo_pago,
                'precio_compra_dolares' => $this->precio_compra_dolares,
                'total_pagado_dolares' => $this->precio_compra_dolares * $this->cantidad,
            ]);

        }
       
          $this->reset(['open']);
          $this->emitTo('administracion.compras.compras-index','render');
  

        notyf()
          ->duration(9000) // 2 seconds
          ->position('y', 'top')
          ->position('x', 'right')
          ->addSuccess('compra modificada exitosamente');


    }
}
