<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $es_factura ? 'Factura' : 'Ticket' }}</title>
    <style>
        /* RESET TOTAL - SIN MÁRGENES NI PADDING */
        * { margin:0; padding:0; box-sizing:border-box; }
        
        @page { 
            margin: 0 !important; 
            padding: 0 !important; 
            size: 70mm auto !important; 
        }
        
        body { 
            font-family: 'Courier New', monospace !important; 
            font-size: 7pt !important; 
            margin: 0 !important; 
            padding: 1mm 1mm 0 1mm !important; /* Solo padding lateral y superior */
            width: 68mm !important; 
            line-height: 0.85 !important; /* Reducir interlineado al mínimo */
        }
        
        /* UTILIDADES COMPACTAS */
        .center { text-align:center !important; }
        .right { text-align:right !important; }
        .left { text-align:left !important; }
        .bold { font-weight:bold !important; }
        
        /* DIVISORES MÁS FINOS */
        .divider { 
            border-top:1px dashed #000 !important; 
            margin:0.5mm 0 !important; 
            height: 0 !important;
        }
        
        /* TABLAS ULTRA COMPACTAS */
        table { 
            width:100% !important; 
            border-collapse:collapse !important; 
            font-size:7pt !important; 
            margin: 0 !important;
        }
        
        th, td { 
            padding:0.3mm 0 !important; /* Padding mínimo */
            vertical-align:top !important; 
        }
        
        /* ANCHOS DE COLUMNAS OPTIMIZADOS */
        .col-item { width:45% !important; }
        .col-qty { width:10% !important; text-align:center !important; }
        .col-price { width:20% !important; text-align:right !important; }
        .col-total { width:25% !important; text-align:right !important; }
        
        /* ESPACIADO MÍNIMO ENTRE SECCIONES */
        .section { margin-bottom: 0.5mm !important; }
        
        /* FOOTER MÁS COMPACTO */
        .footer { 
            font-size:6pt !important; 
            text-align:center !important; 
            margin-top:1mm !important;
            line-height: 0.9 !important;
        }
        
        /* COMPRIMIR ESPACIOS EN BLANCO */
        .no-space { margin:0 !important; padding:0 !important; }
        
        /* PARA NOMBRES LARGOS - FORZAR LÍNEA ÚNICA */
        .truncate {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            display: block !important;
            max-width: 100% !important;
        }
        
        /* NUEVO: Estilos para impuestos */
        .impuesto-row { font-size: 6.5pt !important; }
        .impuesto-label { color: #333; }
        .impuesto-valor { text-align: right; }
        .impuesto-total { border-top: 1px solid #000; }
    </style>
</head>
<body>
    <!-- EMPRESA - UNA SOLA LÍNEA COMPACTA -->
    <div class="section center no-space">
        <div class="bold" style="font-size:8pt; margin-bottom:0.2mm;">{{ $empresa['nombre'] }}</div>
        <div style="font-size:5.5pt; line-height:0.9;">
            {{ $empresa['direccion'] }} | Tlf: {{ $empresa['telefono'] }}
        </div>
        @if($es_factura)
        <div style="font-size:5.5pt; line-height:0.9;">
            {{ $empresa['tipo_documento'] }}: {{ $empresa['rif'] }}
        </div>
        @endif
    </div>
    
    <div class="divider"></div>
    
    <!-- INFO VENTA - TABLA COMPACTA -->
    <div class="section">
        <div class="center bold" style="font-size:8pt; margin-bottom:0.3mm;">
            {{ $es_factura ? 'FACTURA' : 'TICKET' }} #{{ $numero_factura }}
        </div>
        <table>
            <tr>
                <td class="left" style="width:30%;">Fecha:</td>
                <td class="right" style="width:70%;">{{ $fecha }}</td>
            </tr>
            <tr>
                <td class="left">Cliente:</td>
                <td class="right">{{ substr($venta->cliente->nombre ?? 'General', 0, 15) }}</td>
            </tr>
            <tr>
                <td class="left">Atendió:</td>
                <td class="right">{{ substr($venta->user->name ?? 'Sist', 0, 10) }}</td>
            </tr>
            @if($es_factura && $venta->cliente->tipo_documento)
            <tr>
                <td class="left">{{ $venta->cliente->tipo_documento }}:</td>
                <td class="right">{{ $venta->cliente->nro_documento ?? 'N/A' }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <div class="divider"></div>
    
    <!-- PRODUCTOS - TABLA MUY COMPACTA -->
    <div class="section">
        <table>
            <thead>
                <tr style="border-bottom:1px solid #000;">
                    <th class="col-item left">PRODUCTO</th>
                    <th class="col-qty">CANT</th>
                    <th class="col-price">P.UNIT</th>
                    <th class="col-total">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productosVenta as $item)
                <tr>
                    <td class="col-item left">
                        <div class="truncate" title="{{ $item->producto->nombre ?? 'Prod' }}">
                            {{ substr($item->producto->nombre ?? 'Producto', 0, 18) }}
                        </div>
                        @if($tiene_iva && isset($item->producto->exento))
                        <div style="font-size:5pt;">
                            @if($item->producto->exento == 'No')
                            <span style="color: #d00;">* Con {{ $empresa['nombre_impuesto'] }}</span>
                            @else
                            <span style="color: #080;">* Exento</span>
                            @endif
                        </div>
                        @endif
                    </td>
                    <td class="col-qty">{{ $item->cantidad }}</td>
                    <td class="col-price">${{ number_format($item->precio_dolares, 2) }}</td>
                    <td class="col-total">${{ number_format($item->precio_dolares * $item->cantidad, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="divider"></div>
    
    <!-- TOTALES - CON IMPUESTOS SI APLICA -->
    <div class="section">
        <table>
            <!-- Subtotal sin impuestos -->
            @if($tiene_iva)
            <tr class="impuesto-row">
                <td class="left impuesto-label">Subtotal sin {{ $empresa['nombre_impuesto'] }}:</td>
                <td class="right">${{ number_format($datos_impuestos['subtotal_dolares'], 2) }}</td>
            </tr>
            
            <!-- Monto exento si hay -->
            @if($datos_impuestos['exento'] > 0)
            <tr class="impuesto-row">
                <td class="left impuesto-label">Exento:</td>
                <td class="right">${{ number_format($datos_impuestos['exento'], 2) }}</td>
            </tr>
            @endif
            
            <!-- Impuesto -->
            <tr class="impuesto-row">
                <td class="left impuesto-label">
                    {{ $empresa['nombre_impuesto'] }} ({{ $empresa['porcentaje_iva'] }}%):
                </td>
                <td class="right">${{ number_format($datos_impuestos['impuesto'], 2) }}</td>
            </tr>
            
            <!-- Línea divisoria antes del total -->
            <tr>
                <td colspan="2" style="padding: 0.2mm 0;">
                    <div style="border-top: 1px dotted #666; margin: 0.2mm 0;"></div>
                </td>
            </tr>
            @endif
            
            <!-- Descuento si aplica -->
            @if(($venta->descuento ?? 0) > 0)
            <tr>
                <td class="left">Descuento:</td>
                <td class="right">-${{ number_format($venta->descuento, 2) }}</td>
            </tr>
            @endif
            
            <!-- TOTAL FINAL -->
            <tr class="bold" style="font-size: 8pt;">
                <td class="left">TOTAL:</td>
                <td class="right">${{ number_format($total_dol, 2) }}</td>
            </tr>
            
            <!-- Equivalente en Bs -->
            <tr>
                <td class="left">Equiv.Bs:</td>
                <td class="right">{{ number_format($total_bs, 2) }}</td>
            </tr>
        </table>
        
        <!-- Nota sobre impuestos si aplica -->
        @if($tiene_iva)
        <div style="font-size: 5.5pt; color: #666; margin-top: 0.5mm; text-align: center;">
            Precios incluyen {{ $empresa['nombre_impuesto'] }} ({{ $empresa['porcentaje_iva'] }}%) donde aplica
        </div>
        @endif
    </div>
    
    <div class="divider"></div>
    
    <!-- PAGO - COMPACTO -->
    <div class="section">
        <table>
            <tr>
                <td class="left bold" style="width:40%;">Pago:</td>
                <td class="right" style="width:60%;">
                    @if($venta->metodo_pago == 'debito') Débito
                    @elseif($venta->metodo_pago == 'dol_efec') Dólar Efectivo
                    @elseif($venta->metodo_pago == 'bs_efec') Bs Efectivo
                    @elseif($venta->metodo_pago == 'pago_movil') Pago Móvil
                    @elseif($venta->metodo_pago == 'USDT') USDT
                    @else {{ $venta->metodo_pago }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="left bold">Pagado:</td>
                <td class="right">${{ number_format($venta->monto_pagado_dolares ?? $total_dol, 2) }}</td>
            </tr>
            @if(($venta->deuda_dolares ?? 0) > 0)
            <tr>
                <td class="left bold">Deuda:</td>
                <td class="right">${{ number_format($venta->deuda_dolares, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <div class="divider"></div>
    
    <!-- FOOTER ULTRA COMPACTO -->
    <div class="footer no-space">
        @if($es_factura)
        <div class="bold">FACTURA LEGAL</div>
        <div>Registro Tributario {{ $empresa['tipo_documento'] }}: {{ $empresa['rif'] }}</div>
        @endif
        
        <div>¡Gracias por su compra!</div>
        <div>Vuelva pronto - {{ now()->format('d/m/Y H:i') }}</div>
        <div style="font-size:5pt; margin-top:0.3mm;">
            {{ $es_factura ? 'Factura de venta' : 'Comprobante de venta' }}
        </div>
    </div>
</body>
</html>