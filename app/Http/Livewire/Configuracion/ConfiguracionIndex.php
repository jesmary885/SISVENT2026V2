<?php

namespace App\Http\Livewire\Configuracion;

use App\Models\Negocio;
use Livewire\Component;
use Livewire\WithFileUploads;

class ConfiguracionIndex extends Component
{
    use WithFileUploads;

    public $logo, $empresa, $nombre, $tipo_documento, $documento, $telefono, $email, $direccion;
    
    // NUEVAS PROPIEDADES PARA IMPUESTOS
    public $facturar_con_iva = false;
    public $porcentaje_iva = 16;
    public $nombre_impuesto = 'IVA';

    protected $listeners = ['render' => 'render'];

    protected $rules = [
        'nombre' => 'required|max:50',
        'direccion' => 'required',
        'documento' => 'required|numeric|min:5',
        'tipo_documento' => 'required',
        'telefono' => 'required|numeric|min:11',
        'email' => 'required|max:50|email',
        // NUEVAS REGLAS PARA IMPUESTOS
        'porcentaje_iva' => 'required|numeric|min:0|max:100',
        'nombre_impuesto' => 'required|max:20',
    ];

    public function mount()
    {
        $this->empresa = Negocio::first();

        $this->tipo_documento = $this->empresa->tipo_documento;
        $this->documento = $this->empresa->nro_documento;
        $this->telefono = $this->empresa->telefono;
        $this->nombre = $this->empresa->nombre;
        $this->email = $this->empresa->email;
        $this->direccion = $this->empresa->direccion;
        
        // CARGAR DATOS DE IMPUESTOS
        $this->facturar_con_iva = $this->empresa->facturar_con_iva ?? false;
        $this->porcentaje_iva = $this->empresa->porcentaje_iva ?? 16;
        $this->nombre_impuesto = $this->empresa->nombre_impuesto ?? 'IVA';
    }

    public function render()
    {
        return view('livewire.configuracion.configuracion-index');
    }

    public function update()
    {
        $rules = $this->rules;
        
        // Si no está activado el IVA, no validar porcentaje (pero igual guardar 0)
        if (!$this->facturar_con_iva) {
            unset($rules['porcentaje_iva']);
        }
        
        $this->validate($rules);

        $nombre_imagen = 'logo.png';
        $imagen = 'logo/logo.png';

        if ($this->logo) {
            $this->logo->storeAs('public/logo', $nombre_imagen);
        }

        $this->empresa->update([
            'nombre' => $this->nombre,
            'email' => $this->email,
            'nro_documento' => $this->documento,
            'tipo_documento' => $this->tipo_documento,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'logo' => $imagen,
            // NUEVOS CAMPOS DE IMPUESTOS
            'facturar_con_iva' => $this->facturar_con_iva,
            'porcentaje_iva' => $this->facturar_con_iva ? $this->porcentaje_iva : 0,
            'nombre_impuesto' => $this->nombre_impuesto,
        ]);

        $this->emitTo('sobre-empresa', 'render');
        
        // Emitir evento global para que otros componentes se actualicen
        $this->emit('configuracionImpuestosActualizada');
        
        notyf()
            ->duration(9000)
            ->position('y', 'top')
            ->position('x', 'right')
            ->addSuccess('Configuración actualizada exitosamente');
    }
}