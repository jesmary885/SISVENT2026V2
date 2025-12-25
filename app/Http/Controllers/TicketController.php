<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Tasa;
use App\Models\Negocio; // NUEVO: Importar modelo Negocio
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function generarTicket($id)
    {
        try {
            \Log::info('Generando ticket para venta ID:', ['id' => $id]);
            
            // Buscar la venta con relaciones
            $venta = Venta::with([
                'producto_ventas.producto',
                'cliente', 
                'user',
                'deuda'
            ])->findOrFail($id);
            
            // Obtener productos
            $productosVenta = $venta->producto_ventas;
            
            // Calcular totales
            $total_dol = $venta->total_dolares ?? $productosVenta->sum(function($item) {
                return $item->precio_dolares * $item->cantidad;
            });
            
            $total_bs = $venta->total_bolivares ?? $productosVenta->sum(function($item) {
                return $item->precio_bolivares * $item->cantidad;
            });
            
            // Datos de la empresa y configuración de impuestos
            $empresa = Negocio::first(); // NUEVO: Obtener del modelo Negocio
            
            $empresa_data = [
                'nombre' => $empresa->nombre ?? config('app.name', 'Laravel'),
                'direccion' => $empresa->direccion ?? 'Av. Principal #123',
                'telefono' => $empresa->telefono ?? '0414-123-4567',
                'rif' => $empresa->nro_documento ?? 'J-12345678-9',
                'tipo_documento' => $empresa->tipo_documento ?? 'RIF',
                // NUEVO: Datos de impuestos del negocio
                'facturar_con_iva' => $empresa->facturar_con_iva ?? false,
                'porcentaje_iva' => $empresa->porcentaje_iva ?? 16,
                'nombre_impuesto' => $empresa->nombre_impuesto ?? 'IVA',
            ];
            
            // NUEVO: Datos específicos de impuestos de la venta
            $datos_impuestos = [
                'impuesto' => $venta->impuesto ?? 0,
                'exento' => $venta->exento ?? 0,
                'subtotal_dolares' => $venta->subtotal_dolares ?? ($total_dol - ($venta->impuesto ?? 0)),
            ];
            
            // Verificar si hay productos con IVA aplicado
            $tiene_iva = false;
            if ($empresa_data['facturar_con_iva'] && $datos_impuestos['impuesto'] > 0) {
                $tiene_iva = true;
            }
            
            $data = [
                'venta' => $venta,
                'empresa' => $empresa_data,
                'productosVenta' => $productosVenta,
                'total_dol' => $total_dol,
                'total_bs' => $total_bs,
                'fecha' => $venta->created_at->format('d/m/Y H:i'),
                'numero_factura' => str_pad($venta->id, 8, '0', STR_PAD_LEFT),
                // NUEVO: Datos para manejar IVA
                'datos_impuestos' => $datos_impuestos,
                'tiene_iva' => $tiene_iva,
                'es_factura' => $venta->tipo_comprobante == 'factura', // Para diferenciar ticket vs factura
            ];
            
            // Determinar qué vista usar
            $vista = 'pdf.ticket';
            if ($venta->tipo_comprobante == 'factura') {
                $vista = 'pdf.factura'; // Vista de factura con más detalles
            }
            
            $pdf = Pdf::loadView($vista, $data);
            
            // Tamaño según tipo de comprobante
            if ($venta->tipo_comprobante == 'factura') {
                $pdf->setPaper('A4', 'portrait'); // Factura en A4
            } else {
                // Tamaño para impresora térmica 80mm
                $pdf->setPaper([0, 0, 210, 99999], 'portrait');
            }
            
            if (request()->has('download')) {
                $nombre = ($venta->tipo_comprobante == 'factura' ? 'factura' : 'ticket') . "-{$venta->id}.pdf";
                return $pdf->download($nombre);
            } elseif (request()->has('print')) {
                return $pdf->stream("ticket-{$venta->id}.pdf", [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="ticket-' . $venta->id . '.pdf"'
                ]);
            }
            
            return $pdf->stream("ticket-{$venta->id}.pdf");
            
        } catch (\Exception $e) {
            \Log::error('Error generando ticket:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Error generando ticket'], 500);
        }
    }
    
    // ... resto del código del controlador
}