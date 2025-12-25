<?php

namespace App\Http\Livewire\PuntoVenta;

use App\Models\CarroCompra;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Tasa;
use App\Models\Venta;
use App\Models\Caja;
use App\Models\Negocio; // NUEVO: Importar modelo Negocio
use Livewire\Component;

class PuntoVentaCreate extends Component
{
    protected $listeners = [
        'render' => 'render',
        'carritoActualizado' => 'calcularTotales',
        'ventaFinalizada' => 'limpiarCarrito',
        'ventaPausada' => 'render',
        'ventaReanudada' => 'render',
        'cajaActualizada' => 'verificarCaja',
        'clienteSeleccionado' => 'seleccionarCliente',
         'confirmarReemplazarCarrito' => 'confirmarReemplazarCarrito', // Nuevo
         'obtenerClienteSeleccionado' => 'enviarClienteSeleccionado',
         'configuracionImpuestosActualizada' => 'cargarConfiguracionNegocio', // ACTUALIZADO
    ];
    
    public $open = false, $venta_nro, $cant_producto;
    public $search, $user_id, $monto_recibido;
    public $cliente_general = '1', $cantidad, $presentacion, $marca_id, $categoria, $precio_venta, $precio_compra, $stock_minimo, $vencimiento, $fecha_vencimiento;
    
    // Propiedades para pausar venta
    public $mostrar_modal_pausa = false;
    public $nombre_cliente_pausa = '';
    public $telefono_cliente_pausa = '';
    public $cliente_id = 1;
    
    // Propiedades para selección de cliente
    public $mostrar_buscador_cliente = false;
    public $search_cliente = '';
    public $clientes_encontrados = [];
    public $cliente_seleccionado = null;
    public $mostrar_form_nuevo_cliente = false;
    public $nuevo_cliente_nombre = '';
    public $nuevo_cliente_telefono = '';
    public $nuevo_cliente_email = '';
    public $tieneVentaPausada = false;
    
    // Propiedad para caja
    public $caja_abierta = false;
    public $caja_activa = null;
    
    // Lista de ventas pausadas
    public $ventas_pausadas_lista = [];
    
    // Propiedades para almacenar los totales calculados
    public $total_global = 0;
    public $total_bs = 0;

    // NUEVAS PROPIEDADES PARA IMPUESTOS (desde modelo Negocio)
    public $facturar_con_iva = false;
    public $porcentaje_iva = 16;
    public $nombre_impuesto = 'IVA';
    public $total_iva = 0;
    public $total_exento = 0;
    public $subtotal_sin_iva = 0;
    
    // Propiedad para el modelo Negocio
    public $negocio;

    protected $rules = [
        'nombre' => 'required|max:255|min:2',
        'marca_id' => 'required',
        'presentacion' => 'required',
        'precio_venta' => 'required',
        'vencimiento' => 'required',
        'stock_minimo' => 'required',
    ];

    // Reglas para nuevo cliente
    protected $rules_nuevo_cliente = [
        'nuevo_cliente_nombre' => 'required|min:2|max:100',
        'nuevo_cliente_telefono' => 'required|max:20',
        'nuevo_cliente_email' => 'nullable|email'
    ];

    public function mount()
    {
        $this->user_id = auth()->user()->id;
        $this->verificarCaja();
        $this->cargarVentasPausadas();

          // Cargar configuración del negocio (IMPORTANTE)
        $this->cargarConfiguracionNegocio();
        
        $ultimoRegistro = Venta::latest()->first();
        if($ultimoRegistro) $this->venta_nro = $ultimoRegistro->id + 1;
        else $this->venta_nro = 1;

         $this->tieneVentaPausada = count($this->ventas_pausadas_lista) > 0;

        $this->calcularTotales();
    }

    private function cargarConfiguracionNegocio()
    {
        $this->negocio = Negocio::first();
        
        if ($this->negocio) {
            $this->facturar_con_iva = $this->negocio->facturar_con_iva ?? false;
            $this->porcentaje_iva = $this->negocio->porcentaje_iva ?? 16;
            $this->nombre_impuesto = $this->negocio->nombre_impuesto ?? 'IVA';
        } else {
            // Valores por defecto si no existe el negocio
            $this->facturar_con_iva = false;
            $this->porcentaje_iva = 16;
            $this->nombre_impuesto = 'IVA';
        }
    }

    public function obtenerTotalesConIVA()
    {
        $totales = $this->calcularTotales();
        
        return [
            'total_global' => $this->total_global, // Total CON IVA incluido
            'total_bs' => $this->total_bs,
            'total_iva' => $this->total_iva,
            'total_exento' => $this->total_exento,
            'subtotal_sin_iva' => $this->subtotal_sin_iva,
            'facturar_con_iva' => $this->facturar_con_iva,
            'porcentaje_iva' => $this->porcentaje_iva,
            'nombre_impuesto' => $this->nombre_impuesto
        ];
    }

    /**
     * MÉTODOS PARA SELECCIÓN DE CLIENTE
     */
    public function updatedClienteGeneral($value)
    {
         if ($value == '1') {
        // Si es cliente general
        $this->cliente_id = 1;
        $this->cliente_seleccionado = null;
        $this->mostrar_buscador_cliente = false;
        } else {
            // Si destilda "Cliente General", mostrar buscador
            $this->mostrar_buscador_cliente = true;
            $this->buscarClientes();
        }
    }

    public function enviarClienteSeleccionado()
    {
        $clienteId = $this->cliente_seleccionado ? $this->cliente_seleccionado->id : null;
        
        \Log::info('Solicitud de cliente recibida en Create:', [
            'cliente_id' => $clienteId,
            'cliente_nombre' => $this->cliente_seleccionado ? $this->cliente_seleccionado->nombre : 'General'
        ]);
        
        $this->emit('clienteSeleccionadoDesdeCreate', $clienteId);
    }

    public function updatedSearchCliente($value)
    {
        $this->buscarClientes();
    }

    public function buscarClientes()
    {
        if (empty($this->search_cliente)) {
            $this->clientes_encontrados = Cliente::where('tipo', 'especifico')
                ->orderBy('nombre')
                ->limit(10)
                ->get();
        } else {
            $this->clientes_encontrados = Cliente::where('tipo', 'especifico')
                ->where(function($query) {
                    $query->where('nombre', 'LIKE', '%' . $this->search_cliente . '%')
                          ->orWhere('telefono', 'LIKE', '%' . $this->search_cliente . '%')
                          ->orWhere('email', 'LIKE', '%' . $this->search_cliente . '%');
                })
                ->orderBy('nombre')
                ->limit(20)
                ->get();
        }
    }

    public function seleccionarCliente($clienteId)
    {
        $cliente = Cliente::find($clienteId);

        if ($cliente) {
            $this->cliente_seleccionado = $cliente;
            $this->cliente_id = $cliente->id;
            $this->mostrar_buscador_cliente = false;

                $this->emit('clienteSeleccionadoDesdeCreate', $cliente->id);
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => '✅ Cliente seleccionado: ' . $cliente->nombre
            ]);
        }
    }

    public function mostrarFormNuevoCliente()
    {
        $this->mostrar_form_nuevo_cliente = true;
        $this->reset(['nuevo_cliente_nombre', 'nuevo_cliente_telefono', 'nuevo_cliente_email']);
    }

    public function crearNuevoCliente()
    {
        $this->validate($this->rules_nuevo_cliente);

        $cliente = Cliente::create([
            'nombre' => $this->nuevo_cliente_nombre,
            'telefono' => $this->nuevo_cliente_telefono,
            'email' => $this->nuevo_cliente_email,
            'tipo' => 'especifico'
        ]);

        $this->cliente_seleccionado = $cliente;
        $this->cliente_id = $cliente->id;
        $this->mostrar_form_nuevo_cliente = false;
        $this->mostrar_buscador_cliente = false;

           $this->emit('clienteSeleccionadoDesdeCreate', $cliente->id);
        
        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => '✅ Cliente creado y seleccionado: ' . $cliente->nombre
        ]);
    }



    /**
     * VERIFICACIÓN DE CAJA
     */
    public function verificarCaja()
    {
        try {
            $this->caja_activa = Caja::abiertas()->first();
            $this->caja_abierta = !is_null($this->caja_activa);
        } catch (\Exception $e) {
            $this->caja_abierta = false;
            $this->caja_activa = null;
        }
    }

    public function cancelarPausa()
    {
        $this->mostrar_modal_pausa = false;
        $this->nombre_cliente_pausa = '';
        $this->telefono_cliente_pausa = '';
        
        $this->dispatchBrowserEvent('notify', [
            'type' => 'info',
            'message' => 'Pausa cancelada'
        ]);
    }

    /**
     * CARGA DE VENTAS PAUSADAS
     */
    public function cargarVentasPausadas()
    {
        try {
            $this->ventas_pausadas_lista = Venta::with(['cliente', 'carroCompra'])
                ->where('estado', Venta::ESTADO_PAUSADA)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($venta) {
                    return [
                        'id' => $venta->id,
                        'cliente_nombre' => $venta->cliente->nombre ?? 'Cliente Desconocido',
                        'telefono' => $venta->cliente->telefono ?? '',
                        'total_productos' => $venta->carroCompra->sum('cantidad'),
                        'total_dolares' => $venta->total_dolares,
                        'total_bolivares' => $venta->total_bolivares,
                        'fecha_pausada' => $venta->created_at->format('H:i')
                    ];
                })->toArray();
        } catch (\Exception $e) {
            $this->ventas_pausadas_lista = [];
        }
    }

        /**
     * MÉTODO PARA PAUSAR VENTA MODIFICADO
     */
    public function pausarVenta()
    {
        // Verificar que hay productos en el carrito
        $carrito = CarroCompra::where('user_id', $this->user_id)
            ->where('estado', 'abierta')
            ->get();

        if ($carrito->isEmpty()) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => '❌ No hay productos en el carrito'
            ]);
            return;
        }

        // Verificar caja abierta
        if (!$this->caja_abierta) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => '❌ No hay caja abierta'
            ]);
            return;
        }

        // Si es cliente general, pedir datos en modal
        if ($this->cliente_general == '1') {
            $this->mostrar_modal_pausa = true;
            return;
        }

        // Si está en modo cliente específico pero no ha seleccionado uno
        if ($this->cliente_general != '1' && !$this->cliente_seleccionado) {
            $this->mostrar_buscador_cliente = true;
            $this->dispatchBrowserEvent('notify', [
                'type' => 'warning',
                'message' => '⚠️ Por favor seleccione un cliente primero'
            ]);
            return;
        }

        // Si tiene cliente específico seleccionado, pausar directamente
        $this->procesarPausaVenta();
    }

    // ... (tus otros métodos: procesarPausaVenta, verificarCaja, cargarVentasPausadas, 
    // reanudarVenta, limpiarCarrito, calcularTotales, etc. se mantienen igual)
    
    /**
     * En el método procesarPausaVenta, modifica la parte del cliente:
     */
   public function procesarPausaVenta()
    {
        /*try {
            // Verificar caja nuevamente
            if (!$this->caja_abierta || !$this->caja_activa) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => '❌ No hay caja abierta'
                ]);
                return;
            }

            // Determinar el cliente ID
            $cliente_id = $this->cliente_id;
            
            // SOLO SI ES CLIENTE GENERAL Y SE INGRESARON DATOS, CREAR CLIENTE
            if ($this->cliente_general == '1' && $this->nombre_cliente_pausa) {
                $cliente = Cliente::create([
                    'nombre' => $this->nombre_cliente_pausa,
                    'telefono' => $this->telefono_cliente_pausa,
                    'tipo' => 'especifico'
                ]);
                $cliente_id = $cliente->id;
            }
            // Si es cliente específico, usar el ya seleccionado
            elseif ($this->cliente_seleccionado) {
                $cliente_id = $this->cliente_seleccionado->id;
            }

            // Obtener productos del carrito
            $carrito = CarroCompra::where('user_id', $this->user_id)
                ->where('estado', 'abierta')
                ->get();

            if ($carrito->isEmpty()) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => '❌ No hay productos en el carrito'
                ]);
                return;
            }

            // Calcular totales
            $totales = $this->calcularTotales();
            
            // Crear registro de venta pausada
            $venta = Venta::create([
                'user_id' => $this->user_id,
                'cliente_id' => $cliente_id,
                'caja_id' => $this->caja_activa->id,
                'total_dolares' => $totales['total_global'],
                'total_bolivares' => $totales['total_bs'],
                'estado' => Venta::ESTADO_PAUSADA,
                'metodo_pago' => 'pausada',
            ]);

            // Actualizar estado del carrito a PAUSADA y asignar venta_id
            CarroCompra::where('user_id', $this->user_id)
                ->where('estado', 'abierta')
                ->update([
                    'estado' => 'pausada',
                    'venta_id' => $venta->id
                ]);

            // Limpiar variables
            $this->mostrar_modal_pausa = false;
            $this->nombre_cliente_pausa = '';
            $this->telefono_cliente_pausa = '';
            
            // Limpiar selección de cliente si era general
            if ($this->cliente_general == '1') {
                $this->cliente_general = '1';
                $this->cliente_seleccionado = null;
                $this->cliente_id = 1;
            }

            // Actualizar interfaz
            $this->cant_producto = 0;
            $this->total_global = 0;
            $this->total_bs = 0;
            $this->cargarVentasPausadas();
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => '✅ Venta pausada correctamente'
            ]);
            
            // Emitir eventos para actualizar otros componentes
            $this->emit('ventaPausada');
            $this->emit('carritoActualizado');

        } catch (\Exception $e) {
            \Log::error('Error al pausar venta:', ['error' => $e->getMessage()]);
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => '❌ Error al pausar la venta: ' . $e->getMessage()
            ]);
        }*/

        try {
            // Verificar caja nuevamente
            if (!$this->caja_abierta || !$this->caja_activa) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => '❌ No hay caja abierta'
                ]);
                return;
            }

            // Determinar el cliente ID
            $cliente_id = $this->cliente_id;
            
            // SOLO SI ES CLIENTE GENERAL Y SE INGRESARON DATOS, CREAR CLIENTE
            if ($this->cliente_general == '1' && $this->nombre_cliente_pausa) {
                $cliente = Cliente::create([
                    'nombre' => $this->nombre_cliente_pausa,
                    'telefono' => $this->telefono_cliente_pausa,
                    'tipo' => 'especifico'
                ]);
                $cliente_id = $cliente->id;
            }
            // Si es cliente específico, usar el ya seleccionado
            elseif ($this->cliente_seleccionado) {
                $cliente_id = $this->cliente_seleccionado->id;
            }

            // Obtener productos del carrito
            $carrito = CarroCompra::where('user_id', $this->user_id)
                ->where('estado', 'abierta')
                ->get();

            if ($carrito->isEmpty()) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => '❌ No hay productos en el carrito'
                ]);
                return;
            }

            // Calcular totales CON IMPUESTOS
            $totales = $this->calcularTotales();
            
            // Crear registro de venta pausada
            $venta = Venta::create([
                'user_id' => $this->user_id,
                'cliente_id' => $cliente_id,
                'caja_id' => $this->caja_activa->id,
                'subtotal_dolares' => $totales['subtotal_sin_iva'], // NUEVO
                'total_dolares' => $totales['total_global'],
                'total_bolivares' => $totales['total_bs'],
                'impuesto' => $totales['total_iva'], // NUEVO: guardar el monto del impuesto
                'exento' => $totales['total_exento'], // NUEVO: guardar el monto exento
                'estado' => Venta::ESTADO_PAUSADA,
                'metodo_pago' => 'pausada',
            ]);

            // Actualizar estado del carrito a PAUSADA y asignar venta_id
            CarroCompra::where('user_id', $this->user_id)
                ->where('estado', 'abierta')
                ->update([
                    'estado' => 'pausada',
                    'venta_id' => $venta->id
                ]);

            // Limpiar variables
            $this->mostrar_modal_pausa = false;
            $this->nombre_cliente_pausa = '';
            $this->telefono_cliente_pausa = '';
            
            // Limpiar selección de cliente si era general
            if ($this->cliente_general == '1') {
                $this->cliente_general = '1';
                $this->cliente_seleccionado = null;
                $this->cliente_id = 1;
            }

            // Actualizar interfaz
            $this->cant_producto = 0;
            $this->total_global = 0;
            $this->total_bs = 0;
            $this->total_iva = 0;
            $this->total_exento = 0;
            $this->subtotal_sin_iva = 0;
            $this->cargarVentasPausadas();
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => '✅ Venta pausada correctamente'
            ]);
            
            // Emitir eventos para actualizar otros componentes
            $this->emit('ventaPausada');
            $this->emit('carritoActualizado');

        } catch (\Exception $e) {
            \Log::error('Error al pausar venta:', ['error' => $e->getMessage()]);
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => '❌ Error al pausar la venta: ' . $e->getMessage()
            ]);
        }
    }

        /**
     * Método para limpiar selección de cliente
     */
    public function limpiarSeleccionCliente()
    {
        $this->cliente_seleccionado = null;
        $this->cliente_id = 1;
        $this->cliente_general = '1';
        $this->mostrar_buscador_cliente = false;

           $this->emit('clienteSeleccionadoDesdeCreate', null);
        
        $this->dispatchBrowserEvent('notify', [
            'type' => 'info',
            'message' => 'Cliente deseleccionado'
        ]);
    }

   

    /**
     * MÉTODO PARA REANUDAR VENTA
     */
   public function reanudarVenta($ventaId)
    {
        try {
            $venta = Venta::with(['carroCompra.producto'])->find($ventaId);
            
            if (!$venta || $venta->estado != Venta::ESTADO_PAUSADA) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => '❌ La venta no existe o no está pausada'
                ]);
                return;
            }

            // Limpiar carrito actual SI hay productos
            $carritoActual = CarroCompra::where('user_id', $this->user_id)
                ->where('estado', 'abierta')
                ->get();
                
            if (!$carritoActual->isEmpty()) {
                // Si hay carrito activo, preguntar si quiere reemplazarlo
                $this->dispatchBrowserEvent('confirmar-reemplazo', [
                    'ventaId' => $ventaId,
                    'clienteNombre' => $venta->cliente->nombre ?? 'Cliente Desconocido'
                ]);
                return;
            }

            $this->ejecutarReanudacion($venta);

        } catch (\Exception $e) {
            \Log::error('Error al reanudar venta:', ['error' => $e->getMessage()]);
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => '❌ Error al reanudar la venta'
            ]);
        }
    }

    public function ejecutarReanudacion($venta)
    {
        try {
            // Limpiar carrito actual
            CarroCompra::where('user_id', $this->user_id)
                ->where('estado', 'abierta')
                ->delete();

            // Cargar productos de la venta pausada al carrito
            foreach ($venta->carroCompra as $item) {
                CarroCompra::create([
                    'user_id' => $this->user_id,
                    'producto_id' => $item->producto_id,
                    'cantidad' => $item->cantidad,
                    'estado' => 'abierta',
                    'venta_id' => $venta->id
                ]);
            }

            // Eliminar registros pausados del carrito viejo
            CarroCompra::where('venta_id', $venta->id)
                ->where('estado', 'pausada')
                ->delete();

            // Actualizar estado de la venta a ACTIVA
            $venta->update(['estado' => Venta::ESTADO_ACTIVA]);

            // IMPORTANTE: Configurar el cliente CORRECTO de la venta reanudada
            $this->configurarClienteVenta($venta->cliente_id);

            // Actualizar interfaz
            $this->calcularTotales();
            
            // Recargar lista de ventas pausadas
            $this->cargarVentasPausadas();

            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => '✅ Venta reanudada correctamente'
            ]);
            
            $this->emit('ventaReanudada');
            $this->emit('carritoActualizado');

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * MÉTODOS EXISTENTES (SIN CAMBIOS)
     */
    public function limpiarCarrito()
    {
        CarroCompra::where('user_id', $this->user_id)
            ->where('estado', 'abierta')
            ->delete();
        
        $this->cant_producto = 0;
        $this->total_global = 0;
        $this->total_bs = 0;
        $this->total_iva = 0;
        $this->total_exento = 0;
        $this->subtotal_sin_iva = 0;
        $this->cliente_id = 1;
        $this->cliente_general = '1';
    }

    public function updatedSearch($value)
    {
        if ($value) {
            $this->open = true;
        } else {
            $this->open = false;
        }
        
        $this->render();
    }

    public function render()
    {
        // Verificar caja en cada render
        $this->verificarCaja();
        
        if($this->search) {
            $registros = Producto::where('nombre', 'LIKE', '%' . $this->search . '%')
                ->orWhere('cod_barra', 'LIKE', '%' . $this->search . '%')
                ->where('estado', 'Activo')
                ->latest('id')
                ->get();
        } else {
            $registros = [];
        }

        $registros_carro = CarroCompra::where('estado', 'abierta')
            ->where('user_id', $this->user_id)
            ->get();

        if(!$registros_carro || $registros_carro->isEmpty()) {
            $registros_carro = [];
            $this->cant_producto = 0;
        } else {
            $this->cant_producto = CarroCompra::where('estado', 'abierta')
                ->where('user_id', $this->user_id)
                ->sum('cantidad');
        }

        $this->calcularTotales();

        return view('livewire.punto-venta.punto-venta-create', compact('registros', 'registros_carro'));
    }

        /**
     * Configura el cliente según la venta reanudada
     */
    private function configurarClienteVenta($clienteId)
    {
        if ($clienteId == 1) {
            // Si es cliente general
            $this->cliente_id = 1;
            $this->cliente_general = '1';
            $this->cliente_seleccionado = null;

             $this->emit('clienteSeleccionadoDesdeCreate', null);

        } else {
            // Si es cliente específico
            $cliente = Cliente::find($clienteId);
            if ($cliente) {
                $this->cliente_id = $cliente->id;
                $this->cliente_general = '0'; // Importante: no usar '1'
                $this->cliente_seleccionado = $cliente;

                $this->emit('clienteSeleccionadoDesdeCreate', $cliente->id);

            } else {
                // Si no existe el cliente, usar general
                $this->cliente_id = 1;
                $this->cliente_general = '1';
                $this->cliente_seleccionado = null;

                 $this->emit('clienteSeleccionadoDesdeCreate', null);
            }
        }
    }

    public function confirmarReemplazarCarrito($ventaId)
    {
        try {
            $venta = Venta::find($ventaId);
            if (!$venta) return;

            // Limpiar carrito actual
            CarroCompra::where('user_id', $this->user_id)
                ->where('estado', 'abierta')
                ->delete();

            $this->ejecutarReanudacion($venta);
            
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => '❌ Error: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Método principal para calcular todos los totales
     */
    public function calcularTotales()
    {
        $registros = CarroCompra::where('user_id', $this->user_id)
            ->where('estado', 'abierta')
            ->with('producto') // Asegurar que carga la relación
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
                // Producto NO exento - calcular IVA usando porcentaje_iva del negocio
                $iva_producto = $subtotal * ($this->porcentaje_iva / 100);
                $total_iva += $iva_producto;
                $total_global += ($subtotal + $iva_producto);
            } else {
                // Producto exento o sistema sin IVA
                $total_exento += $subtotal;
                $total_global += $subtotal;
            }
        }

        $tasa_actual = floatval(Tasa::find(1)->tasa_actual);
        $total_bs = $total_global * $tasa_actual;

        $this->total_global = $total_global;
        $this->total_bs = $total_bs;
        $this->total_iva = $total_iva;
        $this->total_exento = $total_exento;
        $this->subtotal_sin_iva = $subtotal_sin_iva;

        return [
            'total_global' => $this->total_global,
            'total_bs' => $this->total_bs,
            'total_iva' => $this->total_iva,
            'total_exento' => $this->total_exento,
            'subtotal_sin_iva' => $this->subtotal_sin_iva
        ];
    }

    /**
     * Métodos para cálculos individuales
     */
    public function subtotal_dol($product, $cant)
    {
        $producto = Producto::find($product);
        if (!$producto) return 0;
        
        $precio_dolares = floatval($producto->precio_venta) * floatval($cant);
        return $precio_dolares;
    }

    public function subtotal_bol($product, $cant)
    {
        $producto = Producto::find($product);
        if (!$producto) return 0;
        
        $precio_dolares = floatval($producto->precio_venta) * floatval($cant);
        $precio_bs = $precio_dolares * floatval(Tasa::find(1)->tasa_actual);
        return $precio_bs;
    }

    public function precio_bolivares($product)
    {
        $precio_bs = floatval($product) * floatval(Tasa::find(1)->tasa_actual);
        return $precio_bs;
    }

    /**
     * Métodos para la vista (usan las propiedades ya calculadas)
     */
    public function total_pagar_global()
    {
        return $this->total_global;
    }

    public function total_pagar_bs()
    {
        return $this->total_bs;
    }

    /**
     * Método para que otros componentes obtengan los totales
     */
    public function obtenerTotales()
    {
        return $this->calcularTotales();
    }

    public function delete($product)
    {
        $prod_destroy = CarroCompra::where('id', $product)->first();
        if($prod_destroy) {
            $prod_destroy->delete();
            
            $this->calcularTotales();
            $this->emitTo('punto-venta.punto-venta-create', 'render');
            $this->emit('carritoActualizado');
        }
    }
}