<?php

namespace App\Http\Livewire\PuntoVenta;

use App\Models\CarroCompra;
use App\Models\Venta;
use App\Models\Cliente;
use Livewire\Component;

class PuntoVentaCantidades extends Component
{
    protected $listeners = [
        'render' => 'render',
        'carritoActualizado' => 'render',
        'cajaActualizada' => 'render',
        'ventaCreada' => 'ventaCreada' // Nuevo listener
    ];

    public $quantity, $registro, $usuario, $user_id;
    public $qty = 1;
    public $caja_abierta = false;
    public $venta_activa_id = null;
    public $esperando_venta = false;

    public function decrement(){
        if ($this->qty > 1) {
            $this->qty = $this->qty - 1;
        }
    }

    public function increment(){
        if ($this->qty < $this->quantity) {
            $this->qty = $this->qty + 1;
        }
    }

    public function mount($registro, $caja_abierta = false){
        $this->registro = $registro;
        $this->user_id = auth()->user()->id;
        $this->caja_abierta = $caja_abierta;
        $this->quantity = qty_available($this->registro);
        
        $this->obtenerVentaActiva();
    }

    public function obtenerVentaActiva()
    {
        $venta_activa = Venta::where('user_id', $this->user_id)
            ->where('estado', Venta::ESTADO_ACTIVA)
            ->first();
            
        $this->venta_activa_id = $venta_activa ? $venta_activa->id : null;
    }

    public function addItem(){
        // Verificar si la caja está abierta
        if (!$this->caja_abierta) {
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ No puede agregar productos. La caja está cerrada.',
                'type' => 'error'
            ]);
            return;
        }

        // Verificar stock disponible
        if ($this->qty > $this->quantity) {
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ Stock insuficiente. Disponible: ' . $this->quantity,
                'type' => 'error'
            ]);
            return;
        }

        if ($this->qty <= 0) {
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ La cantidad debe ser mayor a 0',
                'type' => 'error'
            ]);
            return;
        }

        // Si no hay venta activa, crear una automáticamente
        if (!$this->venta_activa_id) {
            $this->crearVentaAutomatica();
            return;
        }

        $this->agregarAlCarrito();
    }

    private function crearVentaAutomatica()
    {
        try {
            $this->esperando_venta = true;
            
            // Crear cliente temporal automáticamente
            $cliente = Cliente::create([
                'nombre' => 'Cliente Temporal ' . time(),
                'tipo' => 'temporal',
                'telefono' => '0000000',
                'email' => 'temporal@bodegon.com'
            ]);

            // Crear venta activa
            $venta = new Venta();
            $venta->user_id = $this->user_id;
            $venta->cliente_id = $cliente->id;
            $venta->caja_id = 1; // Asumiendo que la caja principal tiene ID 1
            $venta->estado = Venta::ESTADO_ACTIVA;
            $venta->mesa_ubicacion = 'Automática';
            $venta->total_dolares = 0;
            $venta->total_bolivares = 0;
            $venta->metodo_pago = 'pendiente';
            $venta->save();

            $this->venta_activa_id = $venta->id;
            $this->esperando_venta = false;

            // Emitir evento para que PuntoVentaCreate se actualice
            $this->emit('ventaCreadaAutomaticamente', $venta->id, $cliente->id);
            
            // Ahora agregar el producto al carrito
            $this->agregarAlCarrito();

        } catch (\Exception $e) {
            $this->esperando_venta = false;
            \Log::error('Error al crear venta automática:', [
                'error' => $e->getMessage()
            ]);
            
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ Error al crear venta: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    private function agregarAlCarrito()
    {
        try {
            // Buscar si el producto ya está en el carrito de esta venta
            $busqueda = CarroCompra::where('producto_id', $this->registro->id)
                ->where('user_id', $this->user_id)
                ->where('venta_id', $this->venta_activa_id)
                ->where('estado', 'abierta')
                ->first();

            if ($busqueda) {
                // Actualizar cantidad existente
                $nueva_cantidad = $busqueda->cantidad + $this->qty;
                
                // Verificar stock nuevamente con la cantidad total
                if ($nueva_cantidad > $this->quantity) {
                    $this->dispatchBrowserEvent('notify', [
                        'message' => '❌ Stock insuficiente. Cantidad en carrito: ' . $busqueda->cantidad . ', Disponible: ' . $this->quantity,
                        'type' => 'error'
                    ]);
                    return;
                }

                $busqueda->update([
                    'cantidad' => $nueva_cantidad
                ]);
                
                $mensaje = "✅ {$this->registro->nombre} actualizado: {$busqueda->cantidad} unidades";
            } else {
                // Crear nuevo item en el carrito
                $producto_venta = new CarroCompra();
                $producto_venta->producto_id = $this->registro->id;
                $producto_venta->cantidad = $this->qty;
                $producto_venta->user_id = $this->user_id;
                $producto_venta->venta_id = $this->venta_activa_id;
                $producto_venta->estado = 'abierta';
                $producto_venta->save();
                
                $mensaje = "✅ {$this->registro->nombre} agregado al carrito";
            }

            $this->reset('qty');
            
            // Emitir eventos para actualizar otros componentes
            $this->emit('carritoActualizado');
            $this->emitTo('punto-venta.punto-venta-create', 'carritoActualizado');
            $this->emitTo('punto-venta.punto-venta-create', 'render');

            $this->dispatchBrowserEvent('notify', [
                'message' => $mensaje,
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al agregar producto al carrito:', [
                'error' => $e->getMessage(),
                'producto_id' => $this->registro->id,
                'usuario_id' => $this->user_id
            ]);
            
            $this->dispatchBrowserEvent('notify', [
                'message' => '❌ Error al agregar producto: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    // Cuando se crea una venta desde otro componente
    public function ventaCreada($ventaId)
    {
        $this->venta_activa_id = $ventaId;
        $this->esperando_venta = false;
    }

    public function render()
    {
        $this->obtenerVentaActiva();
        
        return view('livewire.punto-venta.punto-venta-cantidades');
    }
}