<?php

namespace App\Http\Livewire\Inventario;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\ProductoLote;
use App\Models\ProductoPresentaciones;
use App\Models\Proveedor;
use App\Models\Tasa;
use Livewire\Component;

class InventarioAdd extends Component
{
 
    protected $listeners = ['render'];
    public $tasa_actual,$moneda_pago,$user_id,$registro,$proveedores,$proveedor_id,$open = false,$cantidad,$precio_compra_dolares,$precio_compra_bolivares, $tasa_compra,$fecha_vencimiento,$lote_numero,$metodo_pago,$total_dolares;

    protected $rules_bs = [

      'cantidad' => 'required|numeric',
      'precio_compra_bolivares' => 'required|numeric',
      'metodo_pago' => 'required',

    ];

     protected $rules_dol = [

      'cantidad' => 'required|numeric',
      'precio_compra_dolares' => 'required|numeric',
       'metodo_pago' => 'required',
       'proveedor_id' => 'required',


    ];


    
    // protected $rule_fecha = [
    //   'fecha_vencimiento' => 'required',


    // ];

    public function mount(){

      $this->user_id = auth()->user()->id;
      $this->proveedores = Proveedor::all();

      $this->tasa_actual=Tasa::find(1)->tasa_actual;
    }

    public function close(){

        $this->open = false;

    }

    public function save(){

     

       if($this->metodo_pago == 'bs_efec' || $this->metodo_pago == 'pago_movil' ){
          $rules_bs = $this->rules_bs;
          $this->validate($rules_bs);

        }else{

            $rules_dol = $this->rules_dol;
            $this->validate($rules_dol);

        }

        $totalOriginal = 0;
        $totalUsd = 0;
        $moneda = '';
        $precioUnitario = 0;

      // if($this->registro->vencimiento == 'Si') {
      //    $rules = $this->rules;
      //   $this->validate($rules);
      // }

      //   $producto_lote = new ProductoLote();
      //   if($this->registro->vencimiento == 'Si') {
      //   $producto_lote->fecha_vencimiento = $this->fecha_vencimiento;
      //   }
      //   $producto_lote->producto_id = $this->registro->id;
      //   $producto_lote->numero= $this->lote_numero;
      //   $producto_lote->save();

      if(in_array($this->metodo_pago, ['bs_efec','pago_movil'])){

        $moneda = 'VES';
        $precioUnitario = $this->precio_compra_bolivares;
        $totalOriginal = $precioUnitario * $this->cantidad;
        $totalUsd = $totalOriginal / $this->tasa_actual;

    }else{

        $moneda = 'USD';
        $precioUnitario = $this->precio_compra_dolares;
        $totalOriginal = $precioUnitario * $this->cantidad;
        $totalUsd = $totalOriginal;
    }



        /*$compra = new Compra();
        $compra->producto_id = $this->registro->id;
        $compra->user_id= $this->user_id;
        $compra->caja_id= 1;
        $compra->proveedor_id= $this->proveedor_id;
        $compra->cantidad= $this->cantidad;
        $compra->proveedor_id= $this->proveedor_id;
        $compra->tasa_cambio_compra= $this->tasa_actual;
        $compra->total_usd_equivalente= $this->metodo_pago;

       if($this->metodo_pago == 'bs_efec' || $this->metodo_pago == 'pago_movil' ){
          $compra->precio_compra_bolivares= $this->precio_compra_bolivares;
          $compra->total_pagado_bolivares= $this->precio_compra_bolivares * $this->cantidad;

        }else{
          $compra->precio_compra_dolares= $this->precio_compra_dolares;
          $compra->total_pagado_dolares= $this->precio_compra_dolares * $this->cantidad;
        }
       

        $compra->save();*/

        $compra = new Compra();
        $compra->producto_id = $this->registro->id;
        $compra->proveedor_id = $this->proveedor_id;
        $compra->user_id = $this->user_id;
        $compra->cantidad = $this->cantidad;
        $compra->moneda_compra = $moneda;
        $compra->metodo_pago = $this->metodo_pago;
        $compra->precio_unitario = $precioUnitario;
        $compra->total_original = $totalOriginal;
        $compra->tasa_cambio_compra = $this->tasa_actual;
        $compra->total_usd_equivalente = $totalUsd;
        $compra->save();

        $presentacion_caja = ProductoPresentaciones::where('producto_id',$this->registro->id)
          ->where('nombre','caja')
          ->where('producto_id',$this->registro->id)
          ->first();

        if($presentacion_caja){

          $cant_cajas_nueva =  $presentacion_caja->cantidad_de_cajas + $this->cantidad;

          $presentacion_caja->update([
            'cantidad_de_cajas' => (float) $cant_cajas_nueva,
          ]);

          $stock_base_nueva = (float) $this->cantidad * (float) $presentacion_caja->factor_base;

          $producto_modif = Producto::find($this->registro->id);
          $producto_modif->stock_base = $producto_modif->stock_base + $stock_base_nueva;
          $producto_modif->save(); 
        }

        else{
          $producto_modif = Producto::find($this->registro->id);
          $producto_modif->stock_base = $producto_modif->stock_base + $this->cantidad;
          $producto_modif->save(); 
        }


        $this->reset(['open','cantidad','proveedor_id','metodo_pago','precio_compra_dolares','precio_compra_bolivares']);

        $this->emitTo('inventario.inventario-index','refreshComponent');

        
        notyf()
          ->duration(9000) // 2 seconds
          ->position('y', 'top')
          ->position('x', 'right')
          ->addSuccess('Producto registrado exitosamente');

    }

    
    public function render()
    {
        return view('livewire.inventario.inventario-add');
    }
}
