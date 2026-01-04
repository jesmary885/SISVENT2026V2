<?php

namespace App\Http\Livewire\PuntoVenta;

use App\Models\CarroCompra;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\ProductoVenta;
use App\Models\Tasa;
use App\Models\Venta;
use App\Models\Deuda;
use App\Models\Negocio; // NUEVO
use Livewire\Component;

class PuntoVentaFinalizar extends Component
{
    public $metodo_pago, $registro, $open = false, $total_dol, $total_bs, $user_id;
    public $monto_cancelado = 1, $montocdol, $montocbs, $cambio, $deuda;
    
    // NUEVAS PROPIEDADES PARA COMPROBANTES Y DEUDAS
    public $tipo_comprobante = 'ninguno';
    
    // PROPIEDADES ESPECÍFICAS PARA DEUDAS
    public $hay_deuda = false;
    public $registrar_deuda = false;
    public $fecha_limite_deuda;
    public $comentario_deuda = '';
    public $comentario_venta = '';
    public $cliente_id_actual;
    public $monto_deuda_dol = 0;
    public $monto_deuda_bs = 0;

    // PROPIEDADES PARA IMPUESTOS
    public $tasa_actual = 0;
    public $facturar_con_iva = false; // NUEVO
    public $porcentaje_iva = 16; // NUEVO
    public $nombre_impuesto = 'IVA'; // NUEVO
    public $total_iva = 0; // NUEVO: monto del impuesto
    public $total_exento = 0; // NUEVO: monto exento
    public $subtotal_sin_iva = 0; // NUEVO: subtotal sin impuesto
    public $negocio; // NUEVO

    protected $rules_dol = [
        'montocdol' => 'required|numeric|min:0',
    ];

    protected $rules_bs = [
        'montocbs' => 'required|numeric|min:0',
    ];

    protected $rules = [
        'metodo_pago' => 'required',
        'tipo_comprobante' => 'required|in:ticket,factura,ninguno',
    ];

    // Listener para actualizar cuando cambie el carrito
    protected $listeners = [
        'carritoActualizado' => 'actualizarTotalesDesdeCreate',
        'clienteSeleccionadoDesdeCreate' => 'actualizarClienteDesdeCreate',
        'configuracionImpuestosActualizada' => 'cargarConfiguracionNegocio' // NUEVO
    ];

    public function mount()
    {
        $this->user_id = auth()->user()->id;
        $this->actualizarTotalesDesdeCreate();
        $this->fecha_limite_deuda = now()->addDays(30)->format('Y-m-d');
        $this->obtenerTasaActual();
        $this->cargarConfiguracionNegocio(); // NUEVO: cargar configuración
    }

    private function obtenerTasaActual()
    {
        try {
            $tasa = \App\Models\Tasa::find(1);
            $this->tasa_actual = $tasa ? floatval($tasa->tasa_actual) : 1;
        } catch (\Exception $e) {
            $this->tasa_actual = 1;
        }
    }

    // NUEVO: Cargar configuración del negocio
    private function cargarConfiguracionNegocio()
    {
        $this->negocio = Negocio::first();
        
        if ($this->negocio) {
            $this->facturar_con_iva = $this->negocio->facturar_con_iva ?? false;
            $this->porcentaje_iva = $this->negocio->porcentaje_iva ?? 16;
            $this->nombre_impuesto = $this->negocio->nombre_impuesto ?? 'IVA';
        }
    }

    public function actualizarClienteDesdeCreate($clienteId)
    {
        $this->cliente_id_actual = $clienteId;
    }

    public function actualizarTotalesDesdeCreate()
    {
        try {
            // Obtener los totales CON IVA desde PuntoVentaCreate
            $totales = $this->emitTo('punto-venta.punto-venta-create', 'obtenerTotalesConIVA');
            
            if (empty($totales)) {
                // Si falla, calcular directamente
                $this->calcularTotalesDirecto();
            } else {
                // Usar los totales con IVA del componente Create
                $this->total_dol = floatval($totales['total_global'] ?? 0); // Total CON IVA
                $this->total_bs = floatval($totales['total_bs'] ?? 0);
                $this->total_iva = floatval($totales['total_iva'] ?? 0);
                $this->total_exento = floatval($totales['total_exento'] ?? 0);
                $this->subtotal_sin_iva = floatval($totales['subtotal_sin_iva'] ?? 0);
                
                // También cargar configuración si viene en los totales
                if (isset($totales['facturar_con_iva'])) {
                    $this->facturar_con_iva = $totales['facturar_con_iva'];
                }
                if (isset($totales['porcentaje_iva'])) {
                    $this->porcentaje_iva = $totales['porcentaje_iva'];
                }
                if (isset($totales['nombre_impuesto'])) {
                    $this->nombre_impuesto = $totales['nombre_impuesto'];
                }
            }
            
            $this->obtenerTasaActual();
            $this->actualizarCalculosPago();
            
        } catch (\Exception $e) {
            // Si hay error, calcular directamente
            $this->calcularTotalesDirecto();
        }
    }

    private function calcularTotalesDirecto()
    {
        $registros = CarroCompra::where('user_id', $this->user_id)
            ->where('estado', 'abierta')
            ->with('producto')
            ->get();

        $total_global = 0;
        $total_iva = 0;
        $total_exento = 0;
        $subtotal_sin_iva = 0;

        foreach($registros as $registro) {
            $producto = $registro->producto;
            $precio = floatval($producto->precio_venta);
            $cantidad = floatval($registro->cantidad);
            $subtotal = $precio * $cantidad;
            
            $subtotal_sin_iva += $subtotal;
            
            // Verificar si el producto está exento de IVA
            if ($this->facturar_con_iva && ($producto->exento ?? 'Si') == 'No') {
                // Producto NO exento - calcular IVA
                $iva_producto = $subtotal * ($this->porcentaje_iva / 100);
                $total_iva += $iva_producto;
                $total_global += ($subtotal + $iva_producto);
            } else {
                // Producto exento o sistema sin IVA
                $total_exento += $subtotal;
                $total_global += $subtotal;
            }
        }

        $this->total_dol = $total_global;
        $this->total_bs = $total_global * $this->tasa_actual;
        $this->total_iva = $total_iva;
        $this->total_exento = $total_exento;
        $this->subtotal_sin_iva = $subtotal_sin_iva;
    }

    private function actualizarCalculosPago()
    {
        $this->cambio = 0;
        $this->deuda = 0;
        
        $monto_pagado = 0;
        $total = 0;
        
        if (in_array($this->metodo_pago, ['debito', 'pago_movil', 'usdt', 'dol_efec'])) {
            $monto_pagado = floatval($this->montocdol) ?? 0;
            $total = floatval($this->total_dol);
        } elseif ($this->metodo_pago === 'bs_efec') {
            $monto_pagado = floatval($this->montocbs) ?? 0;
            $total = floatval($this->total_bs);
        }
        
        if ($monto_pagado >= $total) {
            $this->cambio = $monto_pagado - $total;
            $this->deuda = 0;
        } else {
            $this->cambio = 0;
            $this->deuda = $total - $monto_pagado;
        }
        
        $this->cambio = number_format($this->cambio, 2, '.', '');
        $this->deuda = number_format($this->deuda, 2, '.', '');
        
        if ($this->deuda > 0) {
            $this->hay_deuda = true;
        } else {
            $this->hay_deuda = false;
        }
    }

    public function openModal()
    {
        // Forzar actualización de totales antes de abrir el modal
        $this->actualizarTotalesDesdeCreate();

        $this->emitTo('punto-venta.punto-venta-create', 'obtenerClienteSeleccionado');

        $this->open = true;
    }

    public function close()
    {
        $this->open = false;
        $this->reset('montocdol', 'montocbs', 'cambio', 'deuda', 'tipo_comprobante', 
                    'hay_deuda', 'registrar_deuda', 'comentario_venta', 'comentario_deuda');
        $this->hay_deuda = false;
        $this->registrar_deuda = false;
    }

    public function updatedMetodoPago($value)
    {
        $this->reset(['montocdol', 'montocbs', 'cambio', 'deuda', 
                    'hay_deuda', 'comentario_deuda', 'monto_cancelado']);
        
        $this->monto_cancelado = 1;
        $this->hay_deuda = false;
        
        if ($value === 'dol_efec') {
            $this->deuda = number_format($this->total_dol, 2, '.', '');
        } elseif ($value === 'bs_efec') {
            $this->deuda = number_format($this->total_bs, 2, '.', '');
        } else {
            $this->deuda = number_format($this->total_dol, 2, '.', '');
        }
    }
    
    public function updatedMontoCancelado($value)
    {
        if ($value == 1) {
            if (in_array($this->metodo_pago, ['debito', 'pago_movil', 'usdt'])) {
                $this->montocdol = $this->total_dol;
                $this->montocbs = $this->total_bs;
            }
        }
        $this->actualizarCalculosPago();
    }

    public function updatedMontocdol($value)
    {
        $monto = floatval($value) ?? 0;
        $total = floatval($this->total_dol);
        
        if ($monto >= $total) {
            $this->cambio = $monto - $total;
            $this->deuda = 0;
        } else {
            $this->cambio = 0;
            $this->deuda = $total - $monto;
        }
        
        $this->cambio = number_format($this->cambio, 2, '.', '');
        $this->deuda = number_format($this->deuda, 2, '.', '');
        
        if ($this->deuda > 0) {
            $this->hay_deuda = true;
            $this->monto_deuda_dol = $this->deuda;
            $this->monto_deuda_bs = $this->deuda * $this->tasa_actual;
        } else {
            $this->hay_deuda = false;
            $this->registrar_deuda = false;
        }
    }

    public function updatedMontocbs($value)
    {
        $monto = floatval($value) ?? 0;
        $total = floatval($this->total_bs);
        
        if ($monto >= $total) {
            $this->cambio = $monto - $total;
            $this->deuda = 0;
        } else {
            $this->cambio = 0;
            $this->deuda = $total - $monto;
        }
        
        $this->cambio = number_format($this->cambio, 2, '.', '');
        $this->deuda = number_format($this->deuda, 2, '.', '');
        
        if ($this->deuda > 0) {
            $this->hay_deuda = true;
            $this->monto_deuda_bs = $this->deuda;
            $this->monto_deuda_dol = $this->deuda / $this->tasa_actual;
        } else {
            $this->hay_deuda = false;
            $this->registrar_deuda = false;
        }
    }

    public function updatedHayDeuda($value)
    {
        if ($value) {
            $this->registrar_deuda = true;
        } else {
            $this->registrar_deuda = false;
            $this->comentario_deuda = '';
        }
    }

    public function render()
    {
        return view('livewire.punto-venta.punto-venta-finalizar');
    }

    public function save()
    {
        $rules = $this->rules;
        $this->validate($rules);

         // 1. VERIFICAR SI HAY CAJA ABIERTA
        $caja_activa = \App\Models\Caja::abiertas()->first();
        if (!$caja_activa) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => '❌ No hay caja abierta. Abra una caja antes de finalizar la venta.'
            ]);
            return;
        }

        if ($this->hay_deuda && $this->deuda > 0) {
            $this->validate([
                'fecha_limite_deuda' => 'required|date|after_or_equal:today',
                'comentario_deuda' => 'nullable|string|max:500',
            ], [
                'fecha_limite_deuda.required' => 'La fecha límite es obligatoria cuando hay deuda',
                'fecha_limite_deuda.after_or_equal' => 'La fecha límite debe ser hoy o una fecha futura',
            ]);
        }

        $this->verificarConsistencia();

        $clienteId = $this->cliente_id_actual ?? Cliente::where('tipo', 'general')->first()->id;

        // Calcular montos pagados y deuda
        $monto_pagado_dol = 0;
        $monto_pagado_bs = 0;
        $deuda_dol = 0;
        $deuda_bs = 0;
        
        if ($this->monto_cancelado == 1) {
            // Pago completo
            $monto_pagado_dol = $this->total_dol;
            $monto_pagado_bs = $this->total_bs;
            $deuda_dol = 0;
            $deuda_bs = 0;
        } else {
            if (in_array($this->metodo_pago, ['debito', 'pago_movil', 'usdt', 'dol_efec'])) {
                $monto_pagado_dol = floatval($this->montocdol) ?? 0;
                $monto_pagado_bs = $monto_pagado_dol * $this->tasa_actual;
            } elseif ($this->metodo_pago === 'bs_efec') {
                $monto_pagado_bs = floatval($this->montocbs) ?? 0;
                $monto_pagado_dol = $monto_pagado_bs / $this->tasa_actual;
            }
        }

        // Calcular deuda final
        $deuda_dol = max(0, $this->total_dol - $monto_pagado_dol);
        $deuda_bs = max(0, $this->total_bs - $monto_pagado_bs);

        // Determinar estado de pago
        $estado_pago = ($deuda_dol == 0) ? 'pagado' : 'pendiente';

        // Crear la venta CON DATOS DE IMPUESTOS
        $venta = new Venta();
        $venta->user_id = $this->user_id;
        $venta->subtotal_dolares = $this->subtotal_sin_iva; // Subtotal SIN IVA
        $venta->subtotal_bolivares = $this->subtotal_sin_iva * $this->tasa_actual;
        $venta->total_dolares = $this->total_dol; // Total CON IVA
        $venta->total_bolivares = $this->total_bs;
        $venta->impuesto = $this->total_iva; // Monto del impuesto
        $venta->exento = $this->total_exento; // Monto exento
        $venta->monto_pagado_dolares = $monto_pagado_dol;
        $venta->monto_pagado_bolivares = $monto_pagado_bs;
        $venta->deuda_dolares = $deuda_dol;
        $venta->deuda_bolivares = $deuda_bs;
        $venta->cliente_id = $clienteId;
        $venta->metodo_pago = $this->metodo_pago;
        $venta->tipo_comprobante = $this->tipo_comprobante;
        $venta->estado_pago = $estado_pago;
        $venta->caja_id = $caja_activa->id;
        $venta->comentario = $this->comentario_venta;
        $venta->save();

        // 3. ACTUALIZAR EL SALDO DE LA CAJA
        if ($this->metodo_pago == 'bs_efec') {
            $caja_activa->increment('saldo_bolivares', $monto_pagado_bs);
        } elseif ($this->metodo_pago == 'dol_efec') {
            $caja_activa->increment('saldo_dolares', $monto_pagado_dol);
        }
        // Para otros métodos de pago (tarjeta, transferencia, etc.) no se incrementa el efectivo

        \Log::info('Venta creada y asignada a caja:', [
            'venta_id' => $venta->id,
            'caja_id' => $caja_activa->id,
            'metodo_pago' => $this->metodo_pago,
            'monto_pagado_bs' => $monto_pagado_bs,
            'monto_pagado_dol' => $monto_pagado_dol,
            'nuevo_saldo_caja_bs' => $caja_activa->saldo_bolivares,
            'nuevo_saldo_caja_dol' => $caja_activa->saldo_dolares,
        ]);

        // Registrar deuda si el usuario decidió hacerlo
        if ($this->hay_deuda && $deuda_dol > 0) {
            Deuda::create([
                'venta_id' => $venta->id,
                'cliente_id' => $clienteId,
                'monto_dolares' => $deuda_dol,
                'monto_bolivares' => $deuda_bs,
                'fecha_limite' => $this->fecha_limite_deuda,
                'comentario' => $this->comentario_deuda,
                'estado' => 'pendiente',
                'registrada_por' => $this->user_id,
            ]);

            $this->dispatchBrowserEvent('notify', [
                'type' => 'warning',
                'message' => '⚠️ Venta finalizada con DEUDA registrada: $' . 
                            number_format($deuda_dol, 2) . 
                            ' (Bs ' . number_format($deuda_bs, 2) . ')'
            ]);
        }

        // Procesar productos del carrito
        $registros = CarroCompra::where('user_id', $this->user_id)
            ->where('estado', 'abierta')
            ->get();

        foreach($registros as $registro) {
            $precio_total_bs = floatval($registro->producto->precio_venta) * $this->tasa_actual;

            $producto_venta = new ProductoVenta();
            $producto_venta->venta_id = $venta->id;
            $producto_venta->producto_id = $registro->producto->id;
            $producto_venta->precio_dolares = floatval($registro->producto->precio_venta);
            $producto_venta->precio_bolivares = $precio_total_bs;
            $producto_venta->cantidad = $registro->cantidad;
            $producto_venta->save();

            // Actualizar stock del producto
            $producto = Producto::where('id', $registro->producto->id)->first();
            $cantidad_new = $producto->cantidad - $registro->cantidad;

            $producto->update([
                'cantidad' => $cantidad_new
            ]);

            // Cerrar item del carrito
            $registro->update([
                'estado' => 'cerrada'
            ]);
        }

        $this->reset(['open', 'montocdol', 'montocbs', 'cambio', 'deuda', 'comentario_venta',
                    'hay_deuda', 'registrar_deuda', 'comentario_deuda']);
        
        // EMITIR EVENTOS PARA ACTUALIZAR TODOS LOS COMPONENTES
        $this->emitTo('punto-venta.punto-venta-create', 'ventaFinalizada');
        $this->emitTo('ventas.ventas-index', 'ventaCreada');
        $this->emit('carritoActualizado');
        $this->emit('deudaActualizada');
        
        // Generar comprobante automáticamente si no es "ninguno"
        if ($this->tipo_comprobante != 'ninguno') {
            $this->generarComprobante($venta->id);
        }
        
        notyf()
            ->duration(9000)
            ->position('y', 'top')
            ->position('x', 'right')
            ->addSuccess('Venta finalizada con éxito');
    }

    private function generarComprobante($ventaId)
    {
        session()->put('imprimir_venta_id', $ventaId);
        session()->put('tipo_comprobante', $this->tipo_comprobante);
        
        $ruta = $this->tipo_comprobante == 'ticket' 
            ? route('ticket.pdf', ['venta' => $ventaId])  
            : route('factura.pdf', ['venta' => $ventaId]); 
        
        $this->dispatchBrowserEvent('imprimir-comprobante', [
            'url' => $ruta,
            'venta_id' => $ventaId,
            'tipo' => $this->tipo_comprobante,
            'timestamp' => now()->timestamp
        ]);
    }

    private function verificarConsistencia()
    {
        $registros = CarroCompra::where('user_id', $this->user_id)
            ->where('estado', 'abierta')
            ->get();

        $totalManual = 0;
        
        foreach($registros as $registro) {
            $subtotal = floatval($registro->producto->precio_venta) * floatval($registro->cantidad);
            $totalManual += $subtotal;
        }
        
        $diferencia = abs($totalManual - $this->subtotal_sin_iva);
        
        if ($diferencia > 0.01) {
            throw new \Exception("Inconsistencia en los totales de la venta");
        }
    }
}