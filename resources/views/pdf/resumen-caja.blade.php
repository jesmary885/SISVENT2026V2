<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resumen de Caja - {{ $caja_nombre }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 12px; 
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            padding-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
        }
        
        .header h1 { 
            margin: 0; 
            font-size: 24px; 
            color: #2c3e50; 
            margin-bottom: 5px;
        }
        
        .header .subtitle { 
            font-size: 14px; 
            color: #7f8c8d; 
        }
        
        .estado-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .estado-abierta {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .estado-cerrada {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-box { 
            margin-bottom: 25px; 
            padding: 15px; 
            background-color: #f8f9fa; 
            border-radius: 8px; 
            border: 1px solid #dee2e6; 
        }
        
        .info-row { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 8px; 
            padding-bottom: 5px;
            border-bottom: 1px dashed #dee2e6;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label { 
            font-weight: bold; 
            color: #495057; 
            flex: 1;
        }
        
        .info-value { 
            color: #212529; 
            flex: 1;
            text-align: right;
            font-weight: bold;
        }
        
        .section { 
            margin-bottom: 30px; 
            page-break-inside: avoid;
        }
        
        .section-title { 
            background-color: #2c3e50; 
            color: white; 
            padding: 10px 15px; 
            font-weight: bold; 
            margin-bottom: 15px; 
            border-radius: 5px;
            font-size: 14px;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        
        th { 
            background-color: #34495e; 
            color: white; 
            padding: 10px; 
            text-align: left; 
            font-weight: bold;
            border: 1px solid #2c3e50;
        }
        
        td { 
            padding: 10px; 
            border: 1px solid #dee2e6; 
            vertical-align: top;
        }
        
        tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        
        .total-row { 
            background-color: #ecf0f1 !important; 
            font-weight: bold; 
            border-top: 2px solid #2c3e50 !important; 
        }
        
        .moneda-bs { 
            color: #27ae60; 
            font-weight: bold; 
        }
        
        .moneda-usd { 
            color: #2980b9; 
            font-weight: bold; 
        }
        
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 10px; 
            color: #7f8c8d; 
            border-top: 1px solid #dee2e6; 
            padding-top: 15px;
            page-break-inside: avoid;
        }
        
        .firma-line { 
            width: 60%; 
            border-top: 1px solid #333; 
            margin: 50px auto 10px; 
        }
        
        .text-right { 
            text-align: right; 
        }
        
        .text-center { 
            text-align: center; 
        }
        
        .text-bold { 
            font-weight: bold; 
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .diferencia-positiva {
            color: #27ae60;
        }
        
        .diferencia-negativa {
            color: #e74c3c;
        }
        
        .detalle-ventas {
            font-size: 10px;
        }
        
        .detalle-ventas th {
            font-size: 10px;
            padding: 6px;
        }
        
        .detalle-ventas td {
            font-size: 10px;
            padding: 6px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .logo h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <!-- Logo/Encabezado -->
    <div class="logo">
        <h2>{{ config('app.name', 'Sistema de Ventas') }}</h2>
    </div>
    
    <div class="header">
        <h1>RESUMEN DE CAJA - {{ $caja_nombre }}</h1>
        <div class="subtitle">Código: {{ $caja_id }} | Generado: {{ $fecha }} por {{ $usuario }}</div>
        <div class="estado-badge {{ $estado_caja == 'abierta' ? 'estado-abierta' : 'estado-cerrada' }}">
            {{ $estado_caja == 'abierta' ? 'CAJA ABIERTA' : 'CAJA CERRADA' }}
        </div>
    </div>
    
    <!-- Información de Apertura -->
    @if($apertura)
    <div class="info-box">
        <div class="section-title">INFORMACIÓN DE APERTURA</div>
        <div class="info-row">
            <div class="info-label">Fecha de Apertura:</div>
            <div class="info-value">{{ $apertura['fecha_apertura'] }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Responsable:</div>
            <div class="info-value">{{ $apertura['responsable'] }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Monto Inicial Bs:</div>
            <div class="info-value moneda-bs">Bs {{ number_format($apertura['monto_inicial_bs'], 2) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Monto Inicial $:</div>
            <div class="info-value moneda-usd">$ {{ number_format($apertura['monto_inicial_dolares'], 2) }}</div>
        </div>
    </div>
    @endif
    
    <!-- Resumen Financiero -->
    <div class="section">
        <div class="section-title">RESUMEN FINANCIERO</div>
        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Bolívares</th>
                    <th>Dólares</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Monto Inicial</td>
                    <td class="text-right moneda-bs">Bs {{ number_format($resumen['monto_inicial_bs'], 2) }}</td>
                    <td class="text-right moneda-usd">$ {{ number_format($resumen['monto_inicial_dolares'], 2) }}</td>
                    <td class="text-center">-</td>
                </tr>
                
                <tr>
                    <td>Efectivo Recibido</td>
                    <td class="text-right moneda-bs">Bs {{ number_format($resumen['ventas_bs'], 2) }}</td>
                    <td class="text-right moneda-usd">$ {{ number_format($resumen['ventas_dolares'], 2) }}</td>
                    <td class="text-center">{{ $resumen['total_ventas'] }}</td>
                </tr>
                
                @if(($resumen['ventas_usdt'] ?? 0) > 0)
                <tr>
                    <td>USDT Recibido</td>
                    <td class="text-right">-</td>
                    <td class="text-right moneda-usd">$ {{ number_format($resumen['ventas_usdt'], 2) }}</td>
                    <td class="text-center">-</td>
                </tr>
                @endif
                
                @if(($resumen['otros_metodos_bs'] ?? 0) > 0 || ($resumen['otros_metodos_dolares'] ?? 0) > 0)
                <tr>
                    <td>Otros Métodos de Pago</td>
                    <td class="text-right moneda-bs">Bs {{ number_format($resumen['otros_metodos_bs'], 2) }}</td>
                    <td class="text-right moneda-usd">$ {{ number_format($resumen['otros_metodos_dolares'], 2) }}</td>
                    <td class="text-center">-</td>
                </tr>
                @endif
                
                <tr class="total-row">
                    <td>TOTAL GENERAL</td>
                    <td class="text-right moneda-bs">
                        Bs {{ number_format($resumen['monto_inicial_bs'] + $resumen['ventas_bs'] + ($resumen['otros_metodos_bs'] ?? 0), 2) }}
                    </td>
                    <td class="text-right moneda-usd">
                        $ {{ number_format($resumen['monto_inicial_dolares'] + $resumen['ventas_dolares'] + ($resumen['ventas_usdt'] ?? 0) + ($resumen['otros_metodos_dolares'] ?? 0), 2) }}
                    </td>
                    <td class="text-center">{{ $resumen['total_ventas'] }} ventas</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Saldos Actuales -->
    <div class="section">
        <div class="section-title">SALDOS ACTUALES EN CAJA</div>
        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Bolívares</th>
                    <th>Dólares</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Saldo en Caja (Efectivo)</td>
                    <td class="text-right moneda-bs">Bs {{ number_format($resumen['saldo_actual_bs'], 2) }}</td>
                    <td class="text-right moneda-usd">$ {{ number_format($resumen['saldo_actual_dolares'], 2) }}</td>
                </tr>
                
                @php
                    $diferencia_bs = $resumen['saldo_actual_bs'] - $resumen['monto_inicial_bs'];
                    $diferencia_dolares = $resumen['saldo_actual_dolares'] - $resumen['monto_inicial_dolares'];
                @endphp
                
                <tr class="total-row">
                    <td>Diferencia (Saldo - Inicial)</td>
                    <td class="text-right {{ $diferencia_bs >= 0 ? 'diferencia-positiva' : 'diferencia-negativa' }}">
                        {{ $diferencia_bs >= 0 ? '+' : '' }}Bs {{ number_format($diferencia_bs, 2) }}
                    </td>
                    <td class="text-right {{ $diferencia_dolares >= 0 ? 'diferencia-positiva' : 'diferencia-negativa' }}">
                        {{ $diferencia_dolares >= 0 ? '+' : '' }}$ {{ number_format($diferencia_dolares, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Detalle por Método de Pago -->
    @if(!empty($resumen['detalle_metodos_pago']))
    <div class="section">
        <div class="section-title">DETALLE POR MÉTODO DE PAGO</div>
        <table>
            <thead>
                <tr>
                    <th>Método de Pago</th>
                    <th>Cant. Ventas</th>
                    <th>Total Bs</th>
                    <th>Total $</th>
                    <th>Pagado Bs</th>
                    <th>Pagado $</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumen['detalle_metodos_pago'] as $metodo => $detalle)
                <tr>
                    <td>{{ $metodo }}</td>
                    <td class="text-center">{{ $detalle['cantidad'] }}</td>
                    <td class="text-right moneda-bs">Bs {{ number_format($detalle['total_bs'], 2) }}</td>
                    <td class="text-right moneda-usd">$ {{ number_format($detalle['total_dolares'], 2) }}</td>
                    <td class="text-right moneda-bs">Bs {{ number_format($detalle['pagado_bs'], 2) }}</td>
                    <td class="text-right moneda-usd">$ {{ number_format($detalle['pagado_dolares'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    
    <!-- Lista de Ventas -->
    @if(!empty($ventas) && count($ventas) > 0)
    <div class="section page-break">
        <div class="section-title">DETALLE DE VENTAS ({{ count($ventas) }})</div>
        <table class="detalle-ventas">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Método</th>
                    <th>Total $</th>
                    <th>Pagado $</th>
                    <th>Deuda $</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventas as $venta)
                <tr>
                    <td>{{ $venta['id'] }}</td>
                    <td>{{ $venta['fecha'] }}</td>
                    <td>{{ Str::limit($venta['cliente'], 20) }}</td>
                    <td>{{ Str::limit($venta['vendedor'], 15) }}</td>
                    <td>{{ $venta['metodo_pago'] }}</td>
                    <td class="text-right">${{ number_format($venta['total_dolares'], 2) }}</td>
                    <td class="text-right">${{ number_format($venta['monto_pagado_dolares'], 2) }}</td>
                    <td class="text-right">
                        @if($venta['deuda_dolares'] > 0)
                            <span class="diferencia-negativa">${{ number_format($venta['deuda_dolares'], 2) }}</span>
                        @else
                            $0.00
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    
    <!-- Firmas -->
    <div class="section">
        <div class="firma-line"></div>
        <div class="text-center text-bold" style="margin-top: 10px;">Firma del Responsable</div>
        <div class="text-center" style="margin-top: 5px;">{{ $usuario }}</div>
        <div class="text-center" style="margin-top: 20px; font-size: 10px; color: #7f8c8d;">
            Fecha y hora de impresión: {{ $fecha }}
        </div>
    </div>
    
    <!-- Pie de página -->
    <div class="footer">
        <p><strong>{{ config('app.name', 'Sistema de Ventas') }}</strong></p>
        <p>Documento generado automáticamente - Este documento es el comprobante oficial de arqueo de caja</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html>