<?php

namespace App\Http\Livewire\Ventas;

use App\Models\Deuda;
use App\Models\Producto;
use App\Models\ProductoVenta;
use App\Models\Venta;
use App\Models\Negocio;
use Livewire\Component;
use Livewire\WithPagination;

class VentasIndex extends Component
{
    use WithPagination;
    
    public $perPage = 10,$venta_delete;
    public $negocio_config;

    protected $listeners = [
        'render', 
        'ventaCreada' => 'actualizarLista',
        'confirmacion' => 'confirmacion',
        'ventIndex' => 'render'
    ];



    public function mount()
    {
        $this->negocio_config = Negocio::first();
    }

    public function actualizarLista()
    {
        $this->resetPage();
        $this->render();
    }

    public function cant_productos($registro)
    {
        return ProductoVenta::where('venta_id', $registro->id)
            ->sum('cantidad');
    }

    public function verificar_deuda($registro)
    {
        $resultado = Deuda::where('venta_id', $registro->id)
            ->where('estado','pendiente')
            ->first();

        return $resultado ? true : false;
    }

    public function deuda($registro)
    {
        $resultado = Deuda::where('venta_id', $registro->id)
            ->where('estado','pendiente')
            ->first();

        return $resultado ? $resultado->monto_dolares : 0;
    }

    // Métodos para calcular el IVA
    public function iva_dolares($registro)
    {
        // Si ya tienes el impuesto guardado en la venta, usarlo
        if (isset($registro->impuesto) && $registro->impuesto > 0) {
            return floatval($registro->impuesto);
        }
        
        // Si no hay impuesto guardado, calcularlo
        // Primero verificar si el negocio factura con IVA
        if (!$this->negocio_config || !$this->negocio_config->facturar_con_iva) {
            return 0;
        }
        
        // Calcular IVA solo para productos NO exentos
        $porcentaje_iva = $this->negocio_config->porcentaje_iva / 100;
        
        return ProductoVenta::where('venta_id', $registro->id)
            ->with('producto')
            ->get()
            ->sum(function($productoVenta) use ($porcentaje_iva) {
                // Verificar si el producto es EXENTO
                $producto = $productoVenta->producto;
                if ($producto && isset($producto->exento)) {
                    if ($producto->exento == 'No') { // No exento = aplica IVA
                        return (floatval($productoVenta->precio_dolares) * floatval($productoVenta->cantidad)) * $porcentaje_iva;
                    }
                }
                return 0;
            });
    }

    public function iva_bolivares($registro)
    {
        // Si ya tienes el impuesto guardado en la venta, usarlo
        if (isset($registro->impuesto) && $registro->impuesto > 0) {
            return floatval($registro->impuesto);
        }
        
        // Si no hay impuesto guardado, calcularlo
        if (!$this->negocio_config || !$this->negocio_config->facturar_con_iva) {
            return 0;
        }
        
        // Calcular IVA solo para productos NO exentos
        $porcentaje_iva = $this->negocio_config->porcentaje_iva / 100;
        
        return ProductoVenta::where('venta_id', $registro->id)
            ->with('producto')
            ->get()
            ->sum(function($productoVenta) use ($porcentaje_iva) {
                // Verificar si el producto es EXENTO
                $producto = $productoVenta->producto;
                if ($producto && isset($producto->exento)) {
                    if ($producto->exento == 'No') { // No exento = aplica IVA
                        return (floatval($productoVenta->precio_bolivares) * floatval($productoVenta->cantidad)) * $porcentaje_iva;
                    }
                }
                return 0;
            });
    }

    public function subtotal_dol($registro)
    {
        // Usar el subtotal guardado si existe
        if (isset($registro->subtotal_dolares)) {
            return floatval($registro->subtotal_dolares);
        }
        
        // Calcular el subtotal (suma de todos los productos)
        return ProductoVenta::where('venta_id', $registro->id)
            ->get()
            ->sum(function($producto) {
                return floatval($producto->precio_dolares) * floatval($producto->cantidad);
            });
    }

    public function subtotal_bol($registro)
    {
        // Usar el subtotal guardado si existe
        if (isset($registro->subtotal_bolivares)) {
            return floatval($registro->subtotal_bolivares);
        }
        
        // Calcular el subtotal (suma de todos los productos)
        return ProductoVenta::where('venta_id', $registro->id)
            ->get()
            ->sum(function($producto) {
                return floatval($producto->precio_bolivares) * floatval($producto->cantidad);
            });
    }

    // Calcular monto exento (productos sin IVA)
    public function exento_dolares($registro)
    {
        // Si ya tienes el monto exento guardado
        if (isset($registro->exento)) {
            return floatval($registro->exento);
        }
        
        // Calcular monto de productos exentos
        return ProductoVenta::where('venta_id', $registro->id)
            ->with('producto')
            ->get()
            ->sum(function($productoVenta) {
                $producto = $productoVenta->producto;
                if ($producto && isset($producto->exento)) {
                    if ($producto->exento == 'Si') { // Exento = no aplica IVA
                        return floatval($productoVenta->precio_dolares) * floatval($productoVenta->cantidad);
                    }
                }
                // Por defecto, si no se especifica, considerar exento
                return floatval($productoVenta->precio_dolares) * floatval($productoVenta->cantidad);
            });
    }

    public function exento_bolivares($registro)
    {
        // Si ya tienes el monto exento guardado
        if (isset($registro->exento)) {
            return floatval($registro->exento);
        }
        
        // Calcular monto de productos exentos
        return ProductoVenta::where('venta_id', $registro->id)
            ->with('producto')
            ->get()
            ->sum(function($productoVenta) {
                $producto = $productoVenta->producto;
                if ($producto && isset($producto->exento)) {
                    if ($producto->exento == 'Si') { // Exento = no aplica IVA
                        return floatval($productoVenta->precio_bolivares) * floatval($productoVenta->cantidad);
                    }
                }
                // Por defecto, si no se especifica, considerar exento
                return floatval($productoVenta->precio_bolivares) * floatval($productoVenta->cantidad);
            });
    }

    public function total_dolares($registro)
    {
        return floatval($registro->total_dolares);
    }

    public function total_bolivares($registro)
    {
        return floatval($registro->total_bolivares);
    }

    public function render()
    {
        $registros = Venta::latest('id')
            ->paginate($this->perPage);

        return view('livewire.ventas.ventas-index', compact('registros'));
    }

    public function delete($ventaId)
    {
        $this->venta_delete = $ventaId;
        $this->emit('confirm', '¿Esta seguro de eliminar esta venta?','ventas.ventas-index','confirmacion','La venta se ha eliminado.');
    }

    public function confirmacion()
    {
        $producto_venta = ProductoVenta::where('venta_id',$this->venta_delete)
            ->get();

        foreach($producto_venta as $pp){
            $buscar = Producto::where('id',$pp->producto->id)->first();
            $cantNew = $buscar->cantidad + $pp->cantidad;

            $buscar->update([
                'cantidad' => $cantNew
            ]);

            $pp->delete();
        }

        $deuda = Deuda::where('venta_id',$this->venta_delete)->first();
        if($deuda) $deuda->delete();

        $venta_destroy = Venta::where('id',$this->venta_delete)->first();
        $venta_destroy->delete();

        $this->emitTo('ventas.ventas-index', 'render');
        $this->emit('ventas.deudas-index');
        $this->emit('deudaActualizada');
    }
}