<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Tasa;
use App\Models\Negocio; // NUEVO: Importar modelo Negocio
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    public function generarFactura($ventaId)
    {
        try {
            // Buscar la venta con relaciones
            $venta = Venta::with(['producto_ventas.producto', 'cliente', 'user'])
                        ->findOrFail($ventaId);
            
            // Obtener tasa actual
            $tasa = Tasa::find(1);
            $tasa_actual = $tasa ? floatval($tasa->tasa_actual) : 1;
            
            // Obtener datos del negocio
            $negocio = Negocio::first();
            
            $empresa = [
                'nombre' => $negocio->nombre ?? config('app.name', 'Mi Negocio'),
                'direccion' => $negocio->direccion ?? 'Av. Principal #123, Ciudad',
                'telefono' => $negocio->telefono ?? '+58 412-1234567',
                'email' => $negocio->email ?? 'facturacion@minegocio.com',
                'rif' => $negocio->nro_documento ?? 'J-12345678-9',
                'tipo_documento' => $negocio->tipo_documento ?? 'RIF',
                // NUEVO: Datos de impuestos del negocio
                'facturar_con_iva' => $negocio->facturar_con_iva ?? false,
                'porcentaje_iva' => $negocio->porcentaje_iva ?? 16,
                'nombre_impuesto' => $negocio->nombre_impuesto ?? 'IVA',
            ];
            
            // Calcular datos fiscales
            $datos_fiscales = $this->calcularDatosFiscales($venta, $empresa, $tasa_actual);
            
            // Verificar si la venta tiene IVA
            $tiene_iva = $empresa['facturar_con_iva'] && ($venta->impuesto ?? 0) > 0;
            
            $data = [
                'venta' => $venta,
                'tasa_actual' => $tasa_actual,
                'fecha' => $venta->created_at->format('d/m/Y H:i:s'),
                'numero_factura' => 'F-' . str_pad($ventaId, 8, '0', STR_PAD_LEFT),
                'control' => 'CTL-' . strtoupper(uniqid()),
                'empresa' => $empresa,
                'datos_fiscales' => $datos_fiscales,
                'tiene_iva' => $tiene_iva,
                'productosVenta' => $venta->producto_ventas, // Cambié el nombre para consistencia
            ];
            
            $pdf = Pdf::loadView('pdf.factura', $data);
            
            if (request()->has('download')) {
                return $pdf->download("factura-{$ventaId}.pdf");
            } elseif (request()->has('print')) {
                return $pdf->stream("factura-{$ventaId}.pdf", [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="factura-' . $ventaId . '.pdf"'
                ]);
            }
            
            return $pdf->stream("factura-{$ventaId}.pdf");
            
        } catch (\Exception $e) {
            \Log::error('Error generando factura:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Error generando factura'], 500);
        }
    }
    
    /**
     * Método para calcular datos fiscales
     */
    private function calcularDatosFiscales($venta, $empresa, $tasa_actual)
    {
        // Si la venta ya tiene datos de impuestos, usarlos
        if ($venta->impuesto || $venta->exento || $venta->subtotal_dolares) {
            return [
                'subtotal_exento' => $venta->exento ?? 0,
                'subtotal_gravado' => $venta->subtotal_dolares ?? 0,
                'iva' => $venta->impuesto ?? 0,
                'total' => $venta->total_dolares ?? 0,
                'iva_porcentaje' => $empresa['porcentaje_iva'],
                'subtotal_exento_bs' => ($venta->exento ?? 0) * $tasa_actual,
                'subtotal_gravado_bs' => ($venta->subtotal_dolares ?? 0) * $tasa_actual,
                'iva_bs' => ($venta->impuesto ?? 0) * $tasa_actual,
                'total_bs' => ($venta->total_bolivares ?? 0),
            ];
        }
        
        // Si no, calcular manualmente
        $productos = $venta->producto_ventas;
        $subtotalExento = 0;
        $subtotalGravado = 0;
        
        foreach ($productos as $item) {
            $producto = $item->producto;
            $subtotal = $item->precio_dolares * $item->cantidad;
            
            // Verificar si el producto está exento
            if ($empresa['facturar_con_iva'] && ($producto->exento ?? 'Si') == 'No') {
                $subtotalGravado += $subtotal;
            } else {
                $subtotalExento += $subtotal;
            }
        }
        
        $iva = $subtotalGravado * ($empresa['porcentaje_iva'] / 100);
        $total = $subtotalExento + $subtotalGravado + $iva;
        
        return [
            'subtotal_exento' => $subtotalExento,
            'subtotal_gravado' => $subtotalGravado,
            'iva' => $iva,
            'total' => $total,
            'iva_porcentaje' => $empresa['porcentaje_iva'],
            'subtotal_exento_bs' => $subtotalExento * $tasa_actual,
            'subtotal_gravado_bs' => $subtotalGravado * $tasa_actual,
            'iva_bs' => $iva * $tasa_actual,
            'total_bs' => $total * $tasa_actual,
        ];
    }
    
    public function vistaPrevia($ventaId)
    {
        try {
            $venta = Venta::with(['producto_ventas.producto', 'cliente', 'user'])
                        ->findOrFail($ventaId);
            
            $tasa = Tasa::find(1);
            $tasa_actual = $tasa ? floatval($tasa->tasa_actual) : 1;
            
            // Obtener datos del negocio
            $negocio = Negocio::first();
            
            $empresa = [
                'nombre' => $negocio->nombre ?? config('app.name', 'Mi Negocio'),
                'direccion' => $negocio->direccion ?? 'Av. Principal #123, Ciudad',
                'telefono' => $negocio->telefono ?? '+58 412-1234567',
                'email' => $negocio->email ?? 'facturacion@minegocio.com',
                'rif' => $negocio->nro_documento ?? 'J-12345678-9',
                'tipo_documento' => $negocio->tipo_documento ?? 'RIF',
                'facturar_con_iva' => $negocio->facturar_con_iva ?? false,
                'porcentaje_iva' => $negocio->porcentaje_iva ?? 16,
                'nombre_impuesto' => $negocio->nombre_impuesto ?? 'IVA',
            ];
            
            // Calcular datos fiscales
            $datos_fiscales = $this->calcularDatosFiscales($venta, $empresa, $tasa_actual);
            
            return view('pdf.factura-preview', [
                'venta' => $venta,
                'tasa_actual' => $tasa_actual,
                'fecha' => now()->format('d/m/Y H:i:s'),
                'numero_factura' => 'F-' . str_pad($ventaId, 8, '0', STR_PAD_LEFT),
                'empresa' => $empresa,
                'datos_fiscales' => $datos_fiscales,
                'productosVenta' => $venta->producto_ventas,
                'tiene_iva' => $empresa['facturar_con_iva'] && ($venta->impuesto ?? 0) > 0,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en vista previa de factura:', [
                'error' => $e->getMessage()
            ]);
            
            abort(404, 'Factura no encontrada');
        }
    }
}