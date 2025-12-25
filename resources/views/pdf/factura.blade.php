<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $numero_factura }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        @page { margin: 15mm; }
        
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 12pt; 
            line-height: 1.4;
            color: #333;
        }
        
        .container { max-width: 210mm; margin: 0 auto; }
        
        /* Encabezado de factura */
        .header { 
            display: flex; 
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000;
        }
        
        .empresa-info { width: 60%; }
        .factura-info { width: 35%; text-align: right; }
        
        .empresa-nombre { 
            font-size: 24pt; 
            font-weight: bold; 
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .factura-titulo {
            font-size: 28pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .factura-numero {
            font-size: 18pt;
            font-weight: bold;
            color: #e74c3c;
        }
        
        /* Información de cliente */
        .cliente-info {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .cliente-titulo {
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        /* Tabla de productos */
        .productos-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .productos-table th {
            background: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #2c3e50;
        }
        
        .productos-table td {
            padding: 8px 10px;
            border: 1px solid #dee2e6;
        }
        
        .productos-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        
        /* Sección de totales */
        .totales-section {
            margin-top: 30px;
            width: 50%;
            margin-left: auto;
        }
        
        .totales-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totales-table td {
            padding: 8px 10px;
            border: 1px solid #dee2e6;
        }
        
        .totales-table tr:last-child {
            background: #2c3e50;
            color: white;
            font-weight: bold;
        }
        
        /* Sección de impuestos */
        .impuestos-section {
            margin-top: 10px;
            padding: 10px;
            background: #f1f8ff;
            border: 1px solid #c8e1ff;
            border-radius: 5px;
            font-size: 11pt;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 10pt;
            color: #666;
            text-align: center;
        }
        
        /* Estado de pago */
        .estado-pago {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .pagado { background: #d4edda; color: #155724; }
        .pendiente { background: #fff3cd; color: #856404; }
        
        /* Responsive */
        @media print {
            .no-print { display: none; }
            body { font-size: 11pt; }
        }
        
        /* Estilos para impuestos */
        .impuesto-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9pt;
            margin-left: 5px;
        }
        
        .con-iva { background: #f8d7da; color: #721c24; }
        .exento { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <!-- ENCABEZADO -->
        <div class="header">
            <div class="empresa-info">
                <div class="empresa-nombre">{{ $empresa['nombre'] }}</div>
                <div>{{ $empresa['direccion'] }}</div>
                <div>Teléfono: {{ $empresa['telefono'] }}</div>
                <div>{{ $empresa['tipo_documento'] }}: {{ $empresa['rif'] }}</div>
                
                @if($empresa['facturar_con_iva'])
                <div style="margin-top: 5px; font-size: 11pt;">
                    <strong>Contribuyente:</strong> 
                    {{ $empresa['nombre_impuesto'] }} {{ $empresa['porcentaje_iva'] }}%
                </div>
                @endif
            </div>
            
            <div class="factura-info">
                <div class="factura-titulo">FACTURA</div>
                <div class="factura-numero">N° {{ $numero_factura }}</div>
                <div>Fecha: {{ $fecha }}</div>
                <div>Control: {{ $control }}</div>
                <div class="estado-pago {{ $venta->estado_pago == 'pagado' ? 'pagado' : 'pendiente' }}">
                    {{ strtoupper($venta->estado_pago) }}
                </div>
            </div>
        </div>
        
        <!-- INFORMACIÓN DEL CLIENTE -->
        <div class="cliente-info">
            <div class="cliente-titulo">DATOS DEL CLIENTE</div>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 20%;"><strong>Cliente:</strong></td>
                    <td>{{ $venta->cliente->nombre ?? 'Consumidor Final' }}</td>
                </tr>
                @if($venta->cliente->tipo_documento)
                <tr>
                    <td><strong>{{ $venta->cliente->tipo_documento }}:</strong></td>
                    <td>{{ $venta->cliente->nro_documento ?? 'N/A' }}</td>
                </tr>
                @endif
                @if($venta->cliente->telefono)
                <tr>
                    <td><strong>Teléfono:</strong></td>
                    <td>{{ $venta->cliente->telefono }}</td>
                </tr>
                @endif
                @if($venta->cliente->email)
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>{{ $venta->cliente->email }}</td>
                </tr>
                @endif
                <tr>
                    <td><strong>Atendido por:</strong></td>
                    <td>{{ $venta->user->name ?? 'Sistema' }}</td>
                </tr>
            </table>
        </div>
        
        <!-- DETALLE DE PRODUCTOS -->
        <table class="productos-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">DESCRIPCIÓN</th>
                    <th style="width: 10%;" class="text-center">CANT.</th>
                    <th style="width: 15%;" class="text-right">PRECIO UNIT. ($)</th>
                    <th style="width: 15%;" class="text-right">SUBTOTAL ($)</th>
                    @if($tiene_iva)
                    <th style="width: 15%;" class="text-center">IVA</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($productosVenta as $index => $item)
                @php
                    $subtotal = $item->precio_dolares * $item->cantidad;
                    $es_exento = isset($item->producto->exento) && $item->producto->exento == 'Si';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $item->producto->nombre ?? 'Producto' }}
                        @if($tiene_iva && isset($item->producto->exento))
                            <br>
                            @if($item->producto->exento == 'No')
                            <span class="impuesto-badge con-iva">CON {{ $empresa['nombre_impuesto'] }}</span>
                            @else
                            <span class="impuesto-badge exento">EXENTO</span>
                            @endif
                        @endif
                    </td>
                    <td class="text-center">{{ $item->cantidad }}</td>
                    <td class="text-right">${{ number_format($item->precio_dolares, 2) }}</td>
                    <td class="text-right">${{ number_format($subtotal, 2) }}</td>
                    @if($tiene_iva)
                    <td class="text-center">
                        @if(!$es_exento)
                        {{ $empresa['porcentaje_iva'] }}%
                        @else
                        Exento
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- TOTALES CON IMPUESTOS -->
        <div class="totales-section">
            <table class="totales-table">
                <!-- Subtotal sin impuestos -->
                @if($tiene_iva)
                <tr>
                    <td class="bold">Subtotal sin {{ $empresa['nombre_impuesto'] }}:</td>
                    <td class="text-right">${{ number_format($datos_fiscales['subtotal_gravado'], 2) }}</td>
                </tr>
                
                <!-- Monto exento si hay -->
                @if($datos_fiscales['subtotal_exento'] > 0)
                <tr>
                    <td>Exento de {{ $empresa['nombre_impuesto'] }}:</td>
                    <td class="text-right">${{ number_format($datos_fiscales['subtotal_exento'], 2) }}</td>
                </tr>
                @endif
                
                <!-- Impuesto -->
                <tr>
                    <td>{{ $empresa['nombre_impuesto'] }} ({{ $empresa['porcentaje_iva'] }}%):</td>
                    <td class="text-right">${{ number_format($datos_fiscales['iva'], 2) }}</td>
                </tr>
                @endif
                
                <!-- Descuento si aplica -->
                @if(($venta->descuento ?? 0) > 0)
                <tr>
                    <td>Descuento:</td>
                    <td class="text-right">-${{ number_format($venta->descuento, 2) }}</td>
                </tr>
                @endif
                
                <!-- TOTAL FINAL -->
                <tr>
                    <td class="bold">TOTAL A PAGAR:</td>
                    <td class="text-right bold">${{ number_format($datos_fiscales['total'], 2) }}</td>
                </tr>
                
                <!-- Equivalente en Bs -->
                <tr>
                    <td>Equivalente en Bs:</td>
                    <td class="text-right">Bs {{ number_format($datos_fiscales['total_bs'], 2) }}</td>
                </tr>
            </table>
            
            <!-- Desglose de pago -->
            <div class="impuestos-section">
                <div class="bold">INFORMACIÓN DE PAGO:</div>
                <div>
                    <strong>Método de pago:</strong> 
                    {{ ucfirst(str_replace('_', ' ', $venta->metodo_pago)) }}
                </div>
                <div>
                    <strong>Monto pagado:</strong> 
                    ${{ number_format($venta->monto_pagado_dolares ?? $datos_fiscales['total'], 2) }}
                    @if($venta->monto_pagado_bolivares)
                    (Bs {{ number_format($venta->monto_pagado_bolivares, 2) }})
                    @endif
                </div>
                @if(($venta->deuda_dolares ?? 0) > 0)
                <div>
                    <strong>Saldo pendiente:</strong> 
                    ${{ number_format($venta->deuda_dolares, 2) }}
                    (Bs {{ number_format($venta->deuda_bolivares ?? 0, 2) }})
                </div>
                @endif
            </div>
        </div>
        
        <!-- FOOTER -->
        <div class="footer">
            <div class="bold">{{ $empresa['nombre'] }}</div>
            <div>{{ $empresa['direccion'] }} | Tel: {{ $empresa['telefono'] }} | Email: {{ $empresa['email'] }}</div>
            <div>{{ $empresa['tipo_documento'] }}: {{ $empresa['rif'] }}</div>
            
            @if($tiene_iva)
            <div style="margin-top: 10px; padding: 8px; background: #f8f9fa; border-radius: 5px;">
                <strong>INFORMACIÓN TRIBUTARIA:</strong><br>
                Esta factura incluye {{ $empresa['nombre_impuesto'] }} al {{ $empresa['porcentaje_iva'] }}%
                según normativa vigente. Los montos exentos están claramente identificados.
            </div>
            @endif
            
            <div style="margin-top: 15px; font-size: 9pt;">
                <strong>Observaciones:</strong> {{ $venta->comentario ?? 'Ninguna' }}
            </div>
            
            <div style="margin-top: 20px; font-style: italic;">
                "Gracias por su preferencia. Vuelva pronto."
            </div>
            
            <div style="margin-top: 10px; font-size: 8pt; color: #999;">
                Documento generado el {{ now()->format('d/m/Y H:i:s') }}
                | Sistema de Gestión - Factura legal
            </div>
            
            <!-- Código de barras o QR (opcional) -->
            <div style="margin-top: 20px; text-align: center;">
                <div style="font-size: 8pt; margin-bottom: 5px;">
                    Código de control: {{ $control }}
                </div>
                <!-- Aquí podrías agregar un código de barras si tienes generador -->
            </div>
        </div>
    </div>
</body>
</html>