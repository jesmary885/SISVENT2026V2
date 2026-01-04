<?php

namespace App\Http\Livewire\Caja;

use App\Models\Caja;
use App\Models\AperturaCierreCaja;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;


class GestionCaja extends Component
{
    public $caja_activa;
    public $monto_inicial_bs = 0;
    public $monto_inicial_dolares = 0;
    public $mostrar_modal_apertura = false;
    public $mostrar_resumen = false;
    public $resumen;
    public $mostrar_modal_cierre = false; // Nuevo: modal para confirmar cierre
    public $observaciones_cierre = ''; // Nuevo: observaciones para el cierre

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

    // Nuevo método: Mostrar modal de confirmación de cierre
    public function confirmarCerrarCaja()
    {
        if (!$this->caja_activa) {
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ No hay caja activa para cerrar',
                'type' => 'error'
            ]);
            return;
        }

        $this->mostrar_modal_cierre = true;
        $this->observaciones_cierre = 'Cierre normal del día';
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
            
            // Cerrar la caja
            $this->caja_activa->cerrar('Cierre normal del día');
            
            // Cargar caja actualizada
            $this->cargarCajaActiva();
            $this->mostrar_modal_cierre = false;
            $this->observaciones_cierre = '';
            
            // Emitir evento para que otros componentes se actualicen
            $this->emit('cajaActualizada');

            $this->dispatchBrowserEvent('notify', [
                'message' => '✅ Caja cerrada correctamente',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al cerrar caja:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ Error al cerrar la caja: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

// Método para generar PDF usando el mismo patrón que exportarPDF
    private function generarPDFArqueoCaja($resumen, $ventasDia, $aperturaActiva)
    {
        $fechaActual = now()->format('d/m/Y H:i:s');
        $usuarioActual = auth()->user()->name;
        
        // Verificar y formatear fecha de apertura
        $fechaAperturaFormateada = null;
        if ($aperturaActiva && $aperturaActiva->fecha_apertura) {
            // Convertir a Carbon si es string
            if (is_string($aperturaActiva->fecha_apertura)) {
                try {
                    $fechaAperturaFormateada = \Carbon\Carbon::parse($aperturaActiva->fecha_apertura)->format('d/m/Y H:i:s');
                } catch (\Exception $e) {
                    $fechaAperturaFormateada = $aperturaActiva->fecha_apertura;
                }
            } elseif ($aperturaActiva->fecha_apertura instanceof \Carbon\Carbon) {
                $fechaAperturaFormateada = $aperturaActiva->fecha_apertura->format('d/m/Y H:i:s');
            }
        }
        
        // Preparar datos para el PDF
        $datosPDF = [
            'fecha' => $fechaActual,
            'usuario' => $usuarioActual,
            'caja_id' => $this->caja_activa->id,
            'caja_nombre' => $this->caja_activa->nombre ?? 'Caja Principal',
            'observaciones' => $this->observaciones_cierre,
            'apertura' => $aperturaActiva ? [
                'fecha_apertura' => $fechaAperturaFormateada,
                'responsable' => $aperturaActiva->user->name ?? 'N/A',
                'monto_inicial_bs' => $aperturaActiva->monto_inicial_bs,
                'monto_inicial_dolares' => $aperturaActiva->monto_inicial_dolares
            ] : null,
            'cierre' => [
                'fecha_cierre' => now()->format('d/m/Y H:i:s')
            ],
            'resumen' => $resumen,
            'ventas' => $ventasDia->map(function($venta) {
                return [
                    'id' => $venta->id,
                    'fecha' => $venta->created_at->format('H:i:s'),
                    'cliente' => $venta->cliente->nombre ?? 'Cliente General',
                    'vendedor' => $venta->user->name ?? 'N/A',
                    'metodo_pago' => $this->traducirMetodoPago($venta->metodo_pago),
                    'total_dolares' => $venta->total_dolares,
                    'total_bolivares' => $venta->total_bolivares,
                    'monto_pagado_dolares' => $venta->monto_pagado_dolares,
                    'monto_pagado_bolivares' => $venta->monto_pagado_bolivares,
                    'deuda_dolares' => $venta->deuda_dolares,
                    'deuda_bolivares' => $venta->deuda_bolivares,
                ];
            }),
            'detalle_metodos_pago' => $resumen['detalle_metodos_pago'] ?? []
        ];

        \Log::info('Generando PDF con datos:', [
            'cantidad_ventas' => count($datosPDF['ventas']),
            'cantidad_metodos' => count($datosPDF['detalle_metodos_pago']),
        ]);

        try {
            $pdf = \PDF::loadView('pdf.arqueo-caja', $datosPDF)
                ->setPaper('a4', 'portrait');
                
            \Log::info('PDF generado exitosamente');
            return $pdf->output();
            
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de arqueo:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function generarPDFResumen()
    {
        if (!$this->caja_activa) {
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ No hay caja activa',
                'type' => 'error'
            ]);
            return;
        }

        try {
            // Obtener el resumen actualizado
            $resumen = $this->caja_activa->obtenerResumenDia();
            
            // Obtener la apertura activa
            $aperturaActiva = $this->caja_activa->aperturaActiva;
            
            // Obtener ventas del día
            $ventasDia = collect();
            if ($aperturaActiva) {
                $fechaApertura = $aperturaActiva->fecha_apertura;
                if (is_string($fechaApertura)) {
                    $fechaApertura = Carbon::parse($fechaApertura);
                }
                
                $ventasDia = $this->caja_activa->ventas()
                    ->where('created_at', '>=', $fechaApertura)
                    ->with(['cliente', 'user'])
                    ->orderBy('created_at', 'asc')
                    ->get();
            }

            // Función para traducir método de pago
            $traducirMetodoPago = function($metodo) {
                $traducciones = [
                    'bs_efec' => 'Efectivo Bs',
                    'dol_efec' => 'Efectivo $',
                    'usdt' => 'USDT',
                    'debito' => 'Tarjeta Débito',
                    'pago_movil' => 'Pago Móvil',
                    'transferencia' => 'Transferencia',
                    'biopago' => 'Biopago',
                    'zelle' => 'Zelle',
                    'pausada' => 'Venta Pausada'
                ];
                return $traducciones[$metodo] ?? $metodo;
            };

            // Preparar datos para el PDF
            $datosPDF = [
                'fecha' => now()->format('d/m/Y H:i:s'),
                'usuario' => auth()->user()->name,
                'caja_id' => $this->caja_activa->id,
                'caja_nombre' => $this->caja_activa->nombre ?? 'Caja Principal',
                'estado_caja' => $this->caja_activa->status,
                'apertura' => $aperturaActiva ? [
                    'fecha_apertura' => is_string($aperturaActiva->fecha_apertura) 
                        ? Carbon::parse($aperturaActiva->fecha_apertura)->format('d/m/Y H:i:s')
                        : $aperturaActiva->fecha_apertura->format('d/m/Y H:i:s'),
                    'responsable' => $aperturaActiva->user->name ?? 'N/A',
                    'monto_inicial_bs' => $aperturaActiva->monto_inicial_bs,
                    'monto_inicial_dolares' => $aperturaActiva->monto_inicial_dolares
                ] : null,
                'resumen' => $resumen,
                'ventas' => $ventasDia->map(function($venta) use ($traducirMetodoPago) {
                    return [
                        'id' => $venta->id,
                        'fecha' => $venta->created_at->format('H:i:s'),
                        'cliente' => $venta->cliente->nombre ?? 'Cliente General',
                        'vendedor' => $venta->user->name ?? 'N/A',
                        'metodo_pago' => $traducirMetodoPago($venta->metodo_pago),
                        'total_dolares' => $venta->total_dolares,
                        'total_bolivares' => $venta->total_bolivares,
                        'monto_pagado_dolares' => $venta->monto_pagado_dolares,
                        'monto_pagado_bolivares' => $venta->monto_pagado_bolivares,
                        'deuda_dolares' => $venta->deuda_dolares,
                        'deuda_bolivares' => $venta->deuda_bolivares,
                    ];
                })->toArray(),
            ];

            \Log::info('Generando PDF de resumen de caja:', [
                'caja_id' => $this->caja_activa->id,
                'cantidad_ventas' => count($datosPDF['ventas']),
            ]);

            $pdf = Pdf::loadView('pdf.resumen-caja', $datosPDF)
                ->setPaper('a4', 'portrait');

            $nombreArchivo = 'resumen_caja_' . $this->caja_activa->id . '_' . Carbon::now()->format('Y-m-d_His') . '.pdf';

            // Cerrar el modal de resumen
            $this->mostrar_resumen = false;

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $nombreArchivo);

        } catch (\Exception $e) {
            \Log::error('Error generando PDF de resumen de caja:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ Error al generar el PDF: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }


    // Método auxiliar para traducir método de pago
    private function traducirMetodoPago($metodo)
    {
        $traducciones = [
            'bs_efec' => 'Efectivo Bs',
            'dol_efec' => 'Efectivo $',
            'debito' => 'Tarjeta Débito',
            'transferencia' => 'Transferencia',
            'pago_movil' => 'Pago Móvil',
            'usdt' => 'USDT',
            'pausada' => 'Venta Pausada'
        ];
        
        return $traducciones[$metodo] ?? $metodo;
    }

    public function verResumen()
    {
        if ($this->caja_activa) {
            $this->resumen = $this->caja_activa->obtenerResumenDia();
            
            // DEPURACIÓN: Mostrar en logs
            \Log::info('Resumen obtenido:', $this->resumen);
            
            $this->mostrar_resumen = true;
        }
    }

    public function debugVentas()
    {
        if (!$this->caja_activa) {
            dd('No hay caja activa');
        }

        $ventas = $this->caja_activa->ventas()
            ->whereDate('created_at', now()->toDateString())
            ->get();

        dd([
            'caja_id' => $this->caja_activa->id,
            'fecha' => now()->toDateString(),
            'total_ventas' => $ventas->count(),
            'ventas' => $ventas->map(function($venta) {
                return [
                    'id' => $venta->id,
                    'created_at' => $venta->created_at,
                    'metodo_pago' => $venta->metodo_pago,
                    'total_bolivares' => $venta->total_bolivares,
                    'total_dolares' => $venta->total_dolares,
                    'monto_pagado_bolivares' => $venta->monto_pagado_bolivares,
                    'monto_pagado_dolares' => $venta->monto_pagado_dolares,
                    'caja_id' => $venta->caja_id,
                ];
            }),
            'resumen_por_metodo' => [
                'bs_efec' => $ventas->where('metodo_pago', 'bs_efec')->sum('total_bolivares'),
                'dol_efec' => $ventas->where('metodo_pago', 'dol_efec')->sum('total_dolares'),
                'debito' => $ventas->where('metodo_pago', 'debito')->sum('total_dolares'),
                'transferencia' => $ventas->where('metodo_pago', 'transferencia')->sum('total_dolares'),
            ]
        ]);
    }
    
    public function render()
    {
        return view('livewire.caja.gestion-caja');
    }
}