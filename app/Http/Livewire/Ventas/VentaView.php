<?php

namespace App\Http\Livewire\Ventas;

use App\Models\Deuda;
use App\Models\ProductoVenta;
use App\Models\Negocio;
use Livewire\Component;

class VentaView extends Component
{
    protected $listeners = ['render'];
    public $open = false, $venta, $productos;
    
    // Agregar propiedades para totales
    public $subtotal_dol = 0;
    public $subtotal_bol = 0;
    public $iva_dol = 0;
    public $iva_bol = 0;
    public $exento_dol = 0;
    public $exento_bol = 0;
    public $gravable_dol = 0;
    public $gravable_bol = 0;
    public $negocio_config;
    public $porcentaje_iva = 16;

    public function close()
    {
        $this->open = false;
    }

    public function mount($venta)
    {
        $this->venta = $venta;
        $this->productos = ProductoVenta::where('venta_id', $venta->id)->get();
        $this->negocio_config = Negocio::first();
        $this->porcentaje_iva = $this->negocio_config ? $this->negocio_config->porcentaje_iva : 16;
        $this->calcularTotales();
    }

    public function verificar_deuda()
    {
        $resultado = Deuda::where('venta_id', $this->venta->id)
            ->where('estado','pendiente')
            ->first();

        return $resultado ? true : false;
    }

    public function deuda()
    {
        $resultado = Deuda::where('venta_id', $this->venta->id)
            ->where('estado','pendiente')
            ->first();

        return $resultado ? $resultado->monto_dolares : 0;
    }

    /**
     * Calcular subtotales, IVA y montos exentos
     */
    public function calcularTotales()
    {
        $this->subtotal_dol = 0;
        $this->subtotal_bol = 0;
        $this->iva_dol = 0;
        $this->iva_bol = 0;
        $this->exento_dol = 0;
        $this->exento_bol = 0;
        $this->gravable_dol = 0;
        $this->gravable_bol = 0;

        foreach ($this->productos as $producto) {
            $subtotal_dol_producto = floatval($producto->precio_dolares) * floatval($producto->cantidad);
            $subtotal_bol_producto = floatval($producto->precio_bolivares) * floatval($producto->cantidad);
            
            $this->subtotal_dol += $subtotal_dol_producto;
            $this->subtotal_bol += $subtotal_bol_producto;
            
            // Verificar si el producto es exento
            $productoModel = $producto->producto;
            if ($productoModel && isset($productoModel->exento)) {
                if ($productoModel->exento == 'Si') {
                    // Producto exento
                    $this->exento_dol += $subtotal_dol_producto;
                    $this->exento_bol += $subtotal_bol_producto;
                } else {
                    // Producto NO exento (gravable)
                    $this->gravable_dol += $subtotal_dol_producto;
                    $this->gravable_bol += $subtotal_bol_producto;
                    
                    // Calcular IVA solo si el negocio factura con IVA
                    if ($this->negocio_config && $this->negocio_config->facturar_con_iva) {
                        $porcentaje = $this->porcentaje_iva / 100;
                        $this->iva_dol += $subtotal_dol_producto * $porcentaje;
                        $this->iva_bol += $subtotal_bol_producto * $porcentaje;
                    }
                }
            } else {
                // Por defecto, considerar exento si no se especifica
                $this->exento_dol += $subtotal_dol_producto;
                $this->exento_bol += $subtotal_bol_producto;
            }
        }
    }

    public function render()
    {
        return view('livewire.ventas.venta-view');
    }
}