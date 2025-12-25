<?php

namespace App\Http\Livewire\Caja;

use App\Models\Caja;
use App\Models\AperturaCierreCaja;
use Livewire\Component;

class GestionCaja extends Component
{
    public $caja_activa;
    public $monto_inicial_bs = 0;
    public $monto_inicial_dolares = 0;
    public $mostrar_modal_apertura = false;
    public $mostrar_resumen = false;
    public $resumen;

    protected $rules = [
        'monto_inicial_bs' => 'required|numeric|min:0',
        'monto_inicial_dolares' => 'required|numeric|min:0'
    ];

    protected $listeners = ['cajaActualizada' => 'cargarCajaActiva'];

    public function mount()
    {
        $this->cargarCajaActiva();
    }

    public function cargarCajaActiva()
    {
        $this->caja_activa = Caja::abiertas()->first();
        
        if ($this->caja_activa) {
            $this->resumen = $this->caja_activa->obtenerResumenDia();
        }
        
        \Log::info('Caja activa cargada:', ['caja' => $this->caja_activa]);
    }

    public function abrirCaja()
    {
        $this->validate();

        \Log::info('Intentando abrir caja con montos:', [
            'bs' => $this->monto_inicial_bs,
            'dolares' => $this->monto_inicial_dolares
        ]);

        try {
            // Buscar o crear la caja principal
            $caja = Caja::first();
            
            if (!$caja) {
                \Log::info('Creando nueva caja...');
                $caja = Caja::create([
                    'nombre' => 'Caja Principal',
                    'status' => Caja::ESTADO_CERRADA,
                    'saldo_bolivares' => 0,
                    'saldo_dolares' => 0
                ]);
            }

            \Log::info('Caja encontrada/creada:', ['caja_id' => $caja->id, 'estado' => $caja->status]);

            if ($caja->estaAbierta()) {
                $this->dispatchBrowserEvent('notify', [
                    'message' => '❌ La caja ya está abierta',
                    'type' => 'error'
                ]);
                return;
            }

            // Abrir la caja usando el método del modelo
            $caja->abrir($this->monto_inicial_bs, $this->monto_inicial_dolares, auth()->id());

            \Log::info('Caja abierta exitosamente:', [
                'caja_id' => $caja->id,
                'nuevo_estado' => $caja->status
            ]);

            $this->cargarCajaActiva();
            $this->mostrar_modal_apertura = false;
            $this->reset(['monto_inicial_bs', 'monto_inicial_dolares']);

            // Emitir evento para que otros componentes se actualicen
            $this->emit('cajaActualizada');
            
            $this->dispatchBrowserEvent('notify', [
                'message' => '✅ Caja abierta correctamente',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al abrir caja:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ Error al abrir la caja: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function cerrarCaja()
    {
        if (!$this->caja_activa) {
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ No hay caja activa para cerrar',
                'type' => 'error'
            ]);
            return;
        }

        try {
            \Log::info('Cerrando caja:', ['caja_id' => $this->caja_activa->id]);
            
            $this->caja_activa->cerrar('Cierre normal del día');
            $this->cargarCajaActiva();
            
            // Emitir evento para que otros componentes se actualicen
            $this->emit('cajaActualizada');

            $this->dispatchBrowserEvent('notify', [
                'message' => '✅ Caja cerrada correctamente',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al cerrar caja:', [
                'error' => $e->getMessage()
            ]);
            
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ Error al cerrar la caja: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function verResumen()
    {
        if ($this->caja_activa) {
            $this->resumen = $this->caja_activa->obtenerResumenDia();
            $this->mostrar_resumen = true;
        }
    }

    public function render()
    {
        return view('livewire.caja.gestion-caja');
    }
}