<?php

namespace App\Http\Livewire\Administracion\Proveedores;

use App\Models\Proveedor;
use Livewire\Component;

class ProveedoresCreate extends Component
{

    protected $listeners = ['render'];
    public $product_delete,$tipo,$registro,$open = false,$marcas,$nombre_encargado,$nombre_proveedor,$tipo_documento,$nro_documento,$email,$telefono,$direccion;

    protected $rules = [
      'nombre_encargado' => 'required|max:255|min:2',
      'nombre_proveedor' => 'required|max:255|min:2',
      'tipo_documento' => 'required',
      'nro_documento' => 'required|max:255|min:2',
      'nombre_encargado' => 'required',
      'email' => 'required',
      'telefono' => 'required',
      'direccion' => 'required',
    ];


       public function close(){

        

        $this->open = false;


    }

      public function mount(){


        if($this->tipo == 'editar'){


          $this->nombre_encargado = $this->registro->nombre_encargado;
          $this->nombre_proveedor = $this->registro->nombre_proveedor;
          $this->tipo_documento = $this->registro->tipo_documento;
          $this->nro_documento = $this->registro->nro_documento;
          $this->email = $this->registro->email;
          $this->telefono = $this->registro->telefono;
          $this->direccion = $this->registro->direccion;

        }
    }

    public function save(){

      $rules = $this->rules;
      $this->validate($rules);



      if($this->tipo == 'agregar'){

        $proveedor = new Proveedor();

        $proveedor->nombre_encargado = $this->nombre_encargado;
        $proveedor->nombre_proveedor = $this->nombre_proveedor;
        $proveedor->tipo_documento = $this->tipo_documento;
        $proveedor->nro_documento = $this->nro_documento;
        $proveedor->email = $this->email;
        $proveedor->telefono = $this->telefono;
        $proveedor->direccion = $this->direccion;
        $proveedor->save();


        $this->reset(['open','nombre_encargado','nombre_proveedor','tipo_documento','nro_documento','email','telefono','direccion']);
        $this->emitTo('administracion.proveedores.proveedores-index','render');

        
        notyf()
          ->duration(9000) // 2 seconds
          ->position('y', 'top')
          ->position('x', 'right')
          ->addSuccess('Proveedor registrado exitosamente');
       


      }else{

        $this->registro->update([
            'nombre_encargado' => $this->nombre_encargado,
            'nombre_proveedor' => $this->nombre_proveedor,
            'tipo_documento' => $this->tipo_documento,
            'nro_documento' => $this->nro_documento,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
        ]);

          $this->reset(['open']);
          $this->emitTo('administracion.proveedores.proveedores-index','render');
  

        notyf()
          ->duration(9000) // 2 seconds
          ->position('y', 'top')
          ->position('x', 'right')
          ->addSuccess('Proveedor modificado exitosamente');

      }


    }

    public function render()
    {
        return view('livewire.administracion.proveedores.proveedores-create');
    }
}
