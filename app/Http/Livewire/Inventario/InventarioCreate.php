<?php

namespace App\Http\Livewire\Inventario;

use App\Models\Compra;
use App\Models\Marca;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\ProductoLote;
use App\Models\Venta;
use Livewire\Component;

class InventarioCreate extends Component
{

    protected $listeners = ['render'];
    public $product_delete,$opcion_impuesto, $iva = '1', $tipo,$registro,$open = false,$marcas,$nombre,$cod_barra, $estado = '1',$cantidad,$presentacion,$marca_id,$categoria,$precio_venta,$precio_compra,$stock_minimo,$vencimiento,$fecha_vencimiento;


    protected $rules = [
      'nombre' => 'required|max:255|min:2',
      'marca_id' => 'required',
      'presentacion' => 'required',
      'precio_venta' => 'required',
      // 'vencimiento' => 'required',
      'stock_minimo' => 'required',

    ];


     protected $rules_editar = [
      'nombre' => 'required|max:255|min:2',
      'marca_id' => 'required',
      'presentacion' => 'required',
      'precio_venta' => 'required',
      'cantidad' => 'required',
      // 'vencimiento' => 'required',
      'stock_minimo' => 'required',

    ];
    
    // protected $rule_fecha = [
    //   'fecha_vencimiento' => 'required',


    // ];



    public function close(){

        

        $this->open = false;


    }

    public function mount(){

        $this->marcas=Marca::all();

        $this->opcion_impuesto = Negocio::first()->facturar_con_iva;

        if($this->tipo == 'editar'){

          if($this->registro->estado == 'Activo') $this->estado = 1;
          else $this->estado = 0;

          $this->nombre = $this->registro->nombre;
          $this->marca_id = $this->registro->marca_id;
          $this->presentacion = $this->registro->presentacion;
          $this->cantidad = $this->registro->cantidad;
          $this->precio_venta = $this->registro->precio_venta;
          // $this->vencimiento = $this->registro->vencimiento;
          $this->cod_barra = $this->registro->cod_barra;
          $this->stock_minimo = $this->registro->stock_minimo;

        }
    }

    public function save(){

       if($this->tipo == 'editar'){

          $rules_editar = $this->rules_editar;
          $this->validate($rules_editar);

       }

        else{

          $rules = $this->rules;
          $this->validate($rules);

        }

      

      // if($this->vencimiento == 'Si') {
      //    $rules = $this->rules;
      //   $this->validate($rules);
      // }

      if($this->estado == 1) $estado = 'Activo';
      else $estado = 'Inactivo';

      if($this->cod_barra) $cod_barra = $this->cod_barra;
      else  $cod_barra = 'N/A';


      if($this->tipo == 'agregar'){

        
        if($this->iva == true) $exento = 'Si';
        else $exento = 'No';

        $producto = new Producto();
        $producto->nombre = $this->nombre;
        $producto->estado = $estado;
        $producto->marca_id = $this->marca_id;
        $producto->presentacion = $this->presentacion;
        $producto->precio_venta = $this->precio_venta;
        $producto->exento = $exento;
        // $producto->vencimiento = $this->vencimiento;
        $producto->stock_minimo = $this->stock_minimo;
        if($this->cod_barra) $producto->cod_barra = $cod_barra;
        else  $producto->cod_barra = $cod_barra;

        $producto->save();

        $this->reset(['open','nombre','estado','marca_id','presentacion','precio_venta','stock_minimo','cod_barra','iva']);
        $this->emitTo('inventario.inventario-index','render');

        
        notyf()
          ->duration(9000) // 2 seconds
          ->position('y', 'top')
          ->position('x', 'right')
          ->addSuccess('Producto registrado exitosamente');
       


      }else{

        if($this->iva == true) $exento = 'Si';
        else $exento = 'No';

        $this->registro->update([
            'nombre' => $this->nombre,
            'estado' => $estado,
            'marca_id' => $this->marca_id,
            'cantidad' => $this->cantidad,
            'presentacion' => $this->presentacion,
            'precio_venta' => $this->precio_venta,
            'exento'=> $exento,
            // 'vencimiento' => $this->vencimiento,
            'stock_minimo' => $this->stock_minimo,
            'cod_barra' => $cod_barra,
        ]);

          $this->reset(['open']);
          $this->emitTo('inventario.inventario-index','render');
  

        notyf()
          ->duration(9000) // 2 seconds
          ->position('y', 'top')
          ->position('x', 'right')
          ->addSuccess('Producto modificado exitosamente');

      }


    }

    
    public function render()
    {
        return view('livewire.inventario.inventario-create');
    }


    
   

   
}
