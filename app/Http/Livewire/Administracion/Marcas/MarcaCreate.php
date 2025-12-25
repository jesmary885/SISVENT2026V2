<?php

namespace App\Http\Livewire\Administracion\Marcas;

use App\Models\Marca;
use Livewire\Component;

class MarcaCreate extends Component
{

    protected $listeners = ['render'];
    public $open = false,$nombre,$registro,$tipo;

    protected $rules = [
      'nombre' => 'required|max:255|min:2',
    ];


    public function close(){

        $this->open = false;

    }

    public function render()
    {
        return view('livewire.administracion.marcas.marca-create');
    }

    public function mount(){

  

        if($this->tipo == 'editar'){


          $this->nombre = $this->registro->nombre;
      

        }
    }


    public function save(){

      $rules = $this->rules;
      $this->validate($rules);


      if($this->tipo == 'agregar'){

        $marca = new Marca();
        $marca->nombre = $this->nombre;
        $marca->save();

        $this->reset(['open','nombre']);
        $this->emitTo('administracion.marcas.marca-index','render');

        
        notyf()
          ->duration(9000) // 2 seconds
          ->position('y', 'top')
          ->position('x', 'right')
          ->addSuccess('Marca registrada exitosamente');
       


      }else{

        $this->registro->update([
            'nombre' => $this->nombre,
        ]);

          $this->reset(['open']);
          $this->emitTo('administracion.marcas.marca-index','render');
  

        notyf()
          ->duration(9000) // 2 seconds
          ->position('y', 'top')
          ->position('x', 'right')
          ->addSuccess('Marca modificada exitosamente');

      }


    }

}
