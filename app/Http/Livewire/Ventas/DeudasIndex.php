<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;

use App\Models\Deuda;
use App\Models\Cliente;
use App\Models\Venta;

use Livewire\WithPagination;

class DeudasIndex extends Component
{

    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    // Propiedades para filtros
    public $search = '';
    public $filtro_estado = 'pendiente';
    public $filtro_cliente = '';
    public $filtro_fecha = '';
    public $desde_fecha = '';
    public $hasta_fecha = '';
    
    // Propiedades para editar deuda
    public $deuda_seleccionada = null;
    public $showEditModal = false;
    public $showPaymentModal = false;
    
    // Propiedades para edición
    public $monto_dolares_edit;
    public $monto_bolivares_edit;
    public $fecha_limite_edit;
    public $comentario_edit;
    public $estado_edit = 'pendiente';
    
    // Propiedades para pago
    public $fecha_pago;
    public $comentario_pago;
    public $metodo_pago = 'efectivo';
    
    // Propiedades para estadísticas
    public $total_deudas_pendientes = 0;
    public $total_monto_pendiente_dol = 0;
    public $total_monto_pendiente_bs = 0;
    public $deudas_vencidas_count = 0;
    
    protected $rules_edicion = [
        'monto_dolares_edit' => 'required|numeric|min:0',
        'monto_bolivares_edit' => 'required|numeric|min:0',
        'fecha_limite_edit' => 'required|date',
        'comentario_edit' => 'nullable|string|max:500',
        'estado_edit' => 'required|in:pendiente,pagada,cancelada',
    ];
    
    protected $rules_pago = [
        'fecha_pago' => 'required|date',
        'comentario_pago' => 'nullable|string|max:500',
        'metodo_pago' => 'required|in:efectivo,debito,pago_movil,transferencia,otro',
    ];
    
    protected $listeners = ['deudaActualizada' => 'render'];

    public function mount()
    {
        $this->calcularEstadisticas();
        $this->fecha_pago = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function calcularEstadisticas()
    {
        // Total deudas pendientes
        $this->total_deudas_pendientes = Deuda::where('estado', 'pendiente')->count();
        
        // Total monto pendiente
        $deudas_pendientes = Deuda::where('estado', 'pendiente')->get();
        $this->total_monto_pendiente_dol = $deudas_pendientes->sum('monto_dolares');
        $this->total_monto_pendiente_bs = $deudas_pendientes->sum('monto_bolivares');
        
        // Deudas vencidas
        $this->deudas_vencidas_count = Deuda::where('estado', 'pendiente')
            ->where('fecha_limite', '<', now())
            ->count();
    }

    public function abrirModalEditar($deudaId)
    {
        $this->deuda_seleccionada = Deuda::with(['cliente', 'venta', 'usuario'])->find($deudaId);
        
        if ($this->deuda_seleccionada) {
            $this->monto_dolares_edit = $this->deuda_seleccionada->monto_dolares;
            $this->monto_bolivares_edit = $this->deuda_seleccionada->monto_bolivares;
            $this->fecha_limite_edit = $this->deuda_seleccionada->fecha_limite->format('Y-m-d');
            $this->comentario_edit = $this->deuda_seleccionada->comentario;
            $this->estado_edit = $this->deuda_seleccionada->estado;
            
            $this->showEditModal = true;
        }
    }

    public function abrirModalPago($deudaId)
    {
        $this->deuda_seleccionada = Deuda::with(['cliente', 'venta'])->find($deudaId);
        
        if ($this->deuda_seleccionada) {
            $this->showPaymentModal = true;
        }
    }

    public function cerrarModales()
    {
        $this->reset(['showEditModal', 'showPaymentModal', 'deuda_seleccionada']);
        $this->resetValidation();
    }

    public function actualizarDeuda()
    {
        $this->validate($this->rules_edicion);
        
        if ($this->deuda_seleccionada) {
            $this->deuda_seleccionada->update([
                'monto_dolares' => $this->monto_dolares_edit,
                'monto_bolivares' => $this->monto_bolivares_edit,
                'fecha_limite' => $this->fecha_limite_edit,
                'comentario' => $this->comentario_edit,
                'estado' => $this->estado_edit,
            ]);
            
            // Actualizar estado de pago en la venta asociada
            if ($this->deuda_seleccionada->venta) {
                $this->deuda_seleccionada->venta->update([
                    'estado_pago' => $this->estado_edit == 'pagada' ? 'pagado' : 'pendiente',
                    'deuda_dolares' => $this->estado_edit == 'pagada' ? 0 : $this->monto_dolares_edit,
                    'deuda_bolivares' => $this->estado_edit == 'pagada' ? 0 : $this->monto_bolivares_edit,
                ]);
            }
            
            $this->cerrarModales();
            $this->calcularEstadisticas();
            
            session()->flash('message', '✅ Deuda actualizada correctamente.');
            $this->emit('deudaActualizada');
            $this->emit('ventIndex');
        }
    }

    public function registrarPago()
    {
        $this->validate($this->rules_pago);
        
        if ($this->deuda_seleccionada) {
            $this->deuda_seleccionada->update([
                'estado' => 'pagada',
                'fecha_pago' => $this->fecha_pago,
                'comentario_pago' => $this->comentario_pago,
            ]);
            
            // Actualizar venta asociada
            if ($this->deuda_seleccionada->venta) {
                $this->deuda_seleccionada->venta->update([
                    'estado_pago' => 'pagado',
                    'deuda_dolares' => 0,
                    'deuda_bolivares' => 0,
                ]);
            }
            
            $this->cerrarModales();
            $this->calcularEstadisticas();
            
            session()->flash('message', '✅ Pago registrado correctamente.');
            $this->emit('deudaActualizada');
            $this->emit('ventIndex');
        }
    }

    public function cancelarDeuda($deudaId)
    {
        $deuda = Deuda::find($deudaId);
        
        if ($deuda) {
            $deuda->update([
                'estado' => 'cancelada',
                'comentario_pago' => 'Cancelada por el sistema - ' . now()->format('d/m/Y'),
            ]);
            
            // Actualizar venta asociada
            if ($deuda->venta) {
                $deuda->venta->update([
                    'estado_pago' => 'pagado', // Se marca como pagado aunque sea cancelada
                    'deuda_dolares' => 0,
                    'deuda_bolivares' => 0,
                ]);
            }
            
            $this->calcularEstadisticas();
            
            session()->flash('message', '✅ Deuda cancelada correctamente.');
            $this->emit('deudaActualizada');
            $this->emit('ventIndex');
        }
    }

    public function render()
    {
        $query = Deuda::with(['cliente', 'venta', 'usuario'])
            ->when($this->filtro_estado, function($q) {
                return $q->where('estado', $this->filtro_estado);
            })
            ->when($this->filtro_cliente, function($q) {
                return $q->where('cliente_id', $this->filtro_cliente);
            })
            ->when($this->search, function($q) {
                return $q->where(function($query) {
                    $query->whereHas('cliente', function($subquery) {
                        $subquery->where('nombre', 'LIKE', '%' . $this->search . '%')
                                ->orWhere('telefono', 'LIKE', '%' . $this->search . '%');
                    })
                    ->orWhere('id', 'LIKE', '%' . $this->search . '%');
                });
            })
            ->when($this->filtro_fecha == 'vencidas', function($q) {
                return $q->where('fecha_limite', '<', now())
                        ->where('estado', 'pendiente');
            })
            ->when($this->filtro_fecha == 'hoy', function($q) {
                return $q->whereDate('fecha_limite', now());
            })
            ->when($this->filtro_fecha == 'semana', function($q) {
                return $q->whereBetween('fecha_limite', [now(), now()->addDays(7)]);
            })
            ->when($this->desde_fecha && $this->hasta_fecha, function($q) {
                return $q->whereBetween('fecha_limite', [$this->desde_fecha, $this->hasta_fecha]);
            })
            ->orderBy('fecha_limite', 'asc');

        $deudas = $query->paginate(20);
        $clientes = Cliente::where('tipo', 'especifico')->orderBy('nombre')->get();

        return view('livewire.ventas.deudas-index', compact('deudas', 'clientes'));
    }


}
