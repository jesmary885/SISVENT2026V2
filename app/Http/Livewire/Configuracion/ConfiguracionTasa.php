<?php

namespace App\Http\Livewire\Configuracion;

use App\Models\Tasa;
use Livewire\Component;

class ConfiguracionTasa extends Component
{

    protected $listeners = ['render'];


    protected $rules = [

      'tasa' => 'required|numeric',
   

    ];

    public $tasa_actual,$tasa;
    public function render()
    {

        $this->tasa_actual = Tasa::find(1);
        return view('livewire.configuracion.configuracion-tasa');
    }

    public function save(){

        $rules = $this->rules;
        $this->validate($rules);

        $this->tasa_actual->update(
            ['tasa_actual' => $this->tasa]
        );


    }
}
