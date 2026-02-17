<?php

namespace App\Http\Livewire\Inventario;

use App\Models\Compra;
use App\Models\Marca;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\ProductoLote;
use App\Models\ProductoPresentaciones;
use App\Models\Venta;
use Livewire\Component;

class InventarioCreate extends Component
{

    //protected $listeners = ['render'];
    public $product_delete,$opcion_impuesto, $iva = '1', $tipo,$registro,$open = false,$marcas,$nombre,$cod_barra, $estado = '1',$cantidad,$presentacion,$marca_id,$categoria,$precio_venta,$precio_compra,$stock_minimo,$vencimiento,$fecha_vencimiento;

    public $tipo_presentacion; 
    public $precio_unidad;
    public $precio_caja;
    public $unidades_por_caja;
    public $cantidad_cajas; 


    public $stock_inicial; 



   public function  cantunidades(){

        return (float)$this->cantidad_cajas * (float)$this->unidades_por_caja;
    }


   protected function rules()
    {
       $base = [
           'nombre' => 'required|max:255|min:2',
           'marca_id' => 'required',
           'tipo_presentacion' => 'required|in:Unidad,Caja,Kg',
           'stock_minimo' => 'required|numeric|min:0',
       ];

       if ($this->tipo_presentacion === 'Caja') {
           $base['unidades_por_caja'] = 'required|numeric|min:1';
           $base['precio_unidad']     = 'required|numeric|min:0';
           $base['precio_caja']       = 'required|numeric|min:0';
           $base['cantidad_cajas']    = 'required|numeric|min:0';
       } else {
           // Unidad o Kg
           $base['precio_venta']   = 'required|numeric|min:0'; // si mantienes precio_venta como “precio de la presentación principal”
           $base['stock_inicial']  = 'required|numeric|min:0';
       }

       return $base;
   }
    
   protected $rule_fecha = [
     'fecha_vencimiento' => 'required',


   ];



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

          $busqueda_editar = ProductoPresentaciones::where('producto_id',$this->registro->id)
            ->get();

          $caja_editar = 0;
          $kg_editar = 0;
          $unidad_editar = 0;
          

          foreach($busqueda_editar as $b){
            if($b->nombre == 'caja') {
              $precio_caja = $b->precio_usd;
              $cantidad_caja = $b->cantidad_de_cajas;
              $unidades_por_caja = $b->factor_base;
              $caja_editar++;
            }
            if($b->nombre == 'kg') {
              $precio_kg = $b->precio_usd;
              $stock_i = $this->registro->stock_base;
              $kg_editar++;
            }
            if($b->nombre == 'unidad') {
              $precio_unidad = $b->precio_usd;
              $stock_i = $this->registro->stock_base;
              $unidad_editar++;
            }
          }

          if($caja_editar > 0 && $unidad_editar > 0){
            $this->tipo_presentacion = 'Caja';
            $this->cantidad_cajas = $cantidad_caja;
            $this->precio_unidad = $precio_unidad;
            $this->precio_caja = $precio_caja;
            $this->unidades_por_caja = $unidades_por_caja;
          
          }

          if($unidad_editar > 0 && $unidad_editar == 0){

            $this->tipo_presentacion = 'Unidad';
            $this->stock_inicial = $this->registro->stock_base;
            $this->precio_venta = $precio_unidad;
            $this->stock_inicial = $stock_i;
      
          }

          if($kg_editar > 0){

            $this->tipo_presentacion = 'Kg';
            $this->stock_inicial = $this->registro->stock_base;
            $this->precio_venta = $precio_kg;
            $this->stock_inicial = $stock_i;
       

          }



        }
    }


    public function save(){
      $this->validate();

      $estado = $this->estado == 1 ? 'Activo' : 'Inactivo';
      $cod_barra = $this->cod_barra ?: 'N/A';
      $exento = ($this->iva == true) ? 'Si' : 'No';

      if($this->tipo == 'agregar'){

        $producto = new Producto();
        $producto->nombre = $this->nombre;
        $producto->estado = $estado;
        $producto->marca_id = $this->marca_id;
        $producto->exento = $exento;
        $producto->stock_minimo = $this->stock_minimo;
        $producto->cod_barra = $cod_barra;

        // unidad_base y stock_base
        if ($this->tipo_presentacion === 'Kg') {
            $producto->unidad_base = 'kg';
            $producto->stock_base = (float) $this->stock_inicial;
        } else {
            $producto->unidad_base = 'unidad';
            if ($this->tipo_presentacion === 'Caja') {
                $producto->stock_base = (float) $this->cantidad_cajas * (float) $this->unidades_por_caja;
            } else {
                $producto->stock_base = (float) $this->stock_inicial;
            }
        }

        $producto->save();

        // Crear presentaciones
        if ($this->tipo_presentacion === 'Caja') {

            // unidad
            ProductoPresentaciones::create([
                'producto_id' => $producto->id,
                'nombre' => 'unidad',
                'factor_base' => 1,
                'precio_usd' => (float) $this->precio_unidad,
                'activo' => true,
               
            ]);

            // caja
            ProductoPresentaciones::create([
                'producto_id' => $producto->id,
                'nombre' => 'caja',
                'factor_base' => (float) $this->unidades_por_caja,
                'precio_usd' => (float) $this->precio_caja,
                'cantidad_de_cajas' => (float) $this->cantidad_cajas,
                'activo' => true,
            ]);

        } elseif ($this->tipo_presentacion === 'Kg') {

            ProductoPresentaciones::create([
                'producto_id' => $producto->id,
                'nombre' => 'kg',
                'factor_base' => 1,
                'precio_usd' => (float) $this->precio_venta,
                'activo' => true,
            ]);

        } else { // Unidad

            ProductoPresentaciones::create([
                'producto_id' => $producto->id,
                'nombre' => 'unidad',
                'factor_base' => 1,
                'precio_usd' => (float) $this->precio_venta,
                'activo' => true,
            ]);
        }

          $this->reset(['open','nombre','estado','marca_id','presentacion','precio_venta','stock_minimo','cod_barra','iva']);
        //  $this->emitTo('inventario.inventario-index','render');

        $this->emitTo('inventario.inventario-index', 'refreshComponent');


          
          notyf()
            ->duration(9000) // 2 seconds
            ->position('y', 'top')
            ->position('x', 'right')
            ->addSuccess('Producto registrado exitosamente');
      }

      else{

        if ($this->tipo_presentacion === 'Kg') {
            $unidad_base = 'kg';
            $stock_base = (float) $this->stock_inicial;
        } else {
            $unidad_base = 'unidad';
            if ($this->tipo_presentacion === 'Caja') {
                $stock_base = (float) $this->cantidad_cajas * (float) $this->unidades_por_caja;
            } else {
                $stock_base = (float) $this->stock_inicial;
            }
        }

          $this->registro->update([
            'nombre' => $this->nombre,
            'estado' => $estado,
            'marca_id' => $this->marca_id,
            'stock_base' => $stock_base,
            'exento'=> $exento,
            'stock_minimo' => $this->stock_minimo,
            'cod_barra' => $cod_barra,
            'unidad_base' => $unidad_base,
        ]);


          $caja = 0;
          $unidad = 0;
          $kg = 0;

          $busquedas = ProductoPresentaciones::where('producto_id',$this->registro->id)->get();

        if ($this->tipo_presentacion === 'Caja') {

              foreach($busquedas as $busqueda){

                if($busqueda->nombre == 'caja'){
                    $busqueda->update(
                      [
                        'precio_usd' => (float) $this->precio_caja,
                        'factor_base' => (float) $this->unidades_por_caja,
                        'cantidad_de_cajas' => (float) $this->cantidad_cajas,
                      ]
                  );

                  $caja++;
                }

                if($busqueda->nombre == 'unidad'){
                    $busqueda->update(
                      [
                        'precio_usd' => (float) $this->precio_unidad,
                      ]
                  );

                  $unidad++;
                }

                if($busqueda->nombre == 'kg'){
                  $kg++;
                }
              }

              if($caja == 0){

                ProductoPresentaciones::create([
                    'producto_id' => $busqueda->producto_id,
                    'nombre' => 'caja',
                    'factor_base' => (float) $this->unidades_por_caja,
                    'precio_usd' => (float) $this->precio_caja,
                    'activo' => true,
                    'cantidad_de_cajas' => $this->cantidad_cajas,
                ]);

              }

              if($unidad == 0){

                ProductoPresentaciones::create([
                    'producto_id' => $busqueda->producto_id,
                    'nombre' => 'unidad',
                    'factor_base' => (float) $this->unidades_por_caja,
                    'precio_usd' => (float) $this->precio_caja,
                    'activo' => true,
                ]);

              }

              if($kg>0){

                  ProductoPresentaciones::where('producto_id',$this->registro->id)
                    ->where('nombre','kg')
                    ->first()
                    ->delete();

              }
        }

        elseif ($this->tipo_presentacion === 'Unidad') {

            foreach($busquedas as $busqueda){

                if($busqueda->nombre == 'caja'){

                  $caja++;
                }

                if($busqueda->nombre == 'unidad'){
                    $busqueda->update(
                      [
                        'precio_usd' => (float) $this->precio_unidad,
                      ]
                  );

                  $unidad++;
                }

                if($busqueda->nombre == 'kg'){
                   
                  $kg++;
                }
            }

            if($caja == 0){

                 ProductoPresentaciones::where('producto_id',$this->registro->id)
                    ->where('nombre','caja')
                    ->first()
                    ->delete();

            }

            if($unidad == 0){

                ProductoPresentaciones::create([
                    'producto_id' => $busqueda->producto_id,
                    'nombre' => 'unidad',
                    'factor_base' => (float) $this->unidades_por_caja,
                    'precio_usd' => (float) $this->precio_caja,
                    'activo' => true,
                ]);

            }

            if($kg>0){

                  ProductoPresentaciones::where('producto_id',$this->registro->id)
                    ->where('nombre','kg')
                    ->first()
                    ->delete();

            }

        } 
        else {

          foreach($busquedas as $busqueda){

                if($busqueda->nombre == 'caja'){

                  $caja++;
                }

                if($busqueda->nombre == 'unidad'){

                  $unidad++;
                }

                if($busqueda->nombre == 'kg'){

                  $busqueda->update(
                      [
                        'precio_usd' => (float) $this->precio_unidad,
                      ]
                    );
                   
                  $kg++;
                }
          }

          if($caja == 0){

                 ProductoPresentaciones::where('producto_id',$this->registro->id)
                    ->where('nombre','caja')
                    ->first()
                    ->delete();

          }

          if($unidad == 0){

                ProductoPresentaciones::where('producto_id',$this->registro->id)
                    ->where('nombre','kg')
                    ->first()
                    ->delete();

          }

          if($kg>0){

              ProductoPresentaciones::create([
                'producto_id' => $busqueda->producto_id,
                'nombre' => 'kg',
                'factor_base' => 1,
                'precio_usd' => (float) $this->precio_venta,
                'activo' => true,
              ]);
          }

        }

      

          $this->reset(['open']);
          $this->emitTo('inventario.inventario-index','refreshComponent');
  

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
