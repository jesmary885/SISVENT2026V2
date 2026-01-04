<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Arqueo de Caja - {{ $caja_nombre }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #2c3e50; }
        .header .subtitle { font-size: 14px; color: #7f8c8d; }
        .info-box { margin-bottom: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px; border: 1px solid #dee2e6; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .info-label { font-weight: bold; color: #495057; }
        .section { margin-bottom: 25px; }
        .section-title { background: #2c3e50; color: white; padding: 8px; font-weight: bold; margin-bottom: 10px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #34495e; color: white; padding: 8px; text-align: left; }
        td { padding: 8px; border: 1px solid #dee2e6; }
        tr:nth-child(even) { background: #f8f9fa; }
        .total-row { background: #ecf0f1 !important; font-weight: bold; border-top: 2px solid #2c3e50 !important; }
        .moneda-bs { color: #27ae60; font-weight: bold; }
        .moneda-usd { color: #2980b9; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #7f8c8d; border-top: 1px solid #dee2e6; padding-top: 10px; }
        .firma-line { width: 60%; border-top: 1px solid #333; margin: 40px auto 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ARQUEO DE CAJA - {{ $caja_nombre }}</h1>
        <div class="subtitle">Código: {{ $caja_id }} | Generado: {{ $fecha }} por {{ $usuario }}</div>
    </div>
    
    @if($apertura)
    <div class="info-box">
        <div class="info-row">
            <div>
                <span class="info-label">Apertura:</span>
                <span>{{ $apertura['fecha_apertura'] }} por {{ $apertura['responsable'] }}</span>
            </div>
            <div>
                <span class="info-label">Cierre:</span>
                <span>{{ $cierre['fecha_cierre'] ?? now()->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>
        @if($observaciones)
        <div class="info-row">
            <div>
                <span class="info-label">Observaciones:</span>
                <span>{{ $observaciones }}</span>
            </div>
        </div>
        @endif
    </div>
    @endif
    
    <!-- Montos Iniciales -->
    <div class="section">
        <div class="section-title">MONTOS INICIALES</div>
        <table>
            <tr>
                <th>Concepto</th>
                <th>Bolívares</th>
                <th>Dólares</th>
            </tr>
            <tr>
                <td>Monto Inicial</td>
                <td class="text-right moneda-bs">Bs {{ number_format($resumen['monto_inicial_bs'], 2) }}</td>
                <td class="text-right moneda-usd">$ {{ number_format($resumen['monto_inicial_dolares'], 2) }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Resumen de Ventas -->
    <div class="section">
        <div class="section-title">RESUMEN DE VENTAS</div>
        <table>
            <tr>
                <th>Concepto</th>
                <th>Bolívares</th>
                <th>Dólares</th>
                <th>Cantidad</th>
            </tr>
            <tr>
                <td>Efectivo Recibido</td>
                <td class="text-right moneda-bs">Bs {{ number_format($resumen['ventas_bs'], 2) }}</td>
                <td class="text-right moneda-usd">$ {{ number_format($resumen['ventas_dolares'], 2) }}</td>
                <td class="text-center">{{ $resumen['total_ventas'] }}</td>
            </tr>
            @if(($resumen['otros_metodos_bs'] ?? 0) > 0 || ($resumen['otros_metodos_dolares'] ?? 0) > 0)
            <tr>
                <td>Otros Métodos</td>
                <td class="text-right moneda-bs">Bs {{ number_format($resumen['otros_metodos_bs'], 2) }}</td>
                <td class="text-right moneda-usd">$ {{ number_format($resumen['otros_metodos_dolares'], 2) }}</td>
                <td class="text-center">-</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL VENTAS</td>
                <td class="text-right moneda-bs">
                    Bs {{ number_format(($resumen['ventas_bs'] + ($resumen['otros_metodos_bs'] ?? 0)), 2) }}
                </td>
                <td class="text-right moneda-usd">
                    $ {{ number_format(($resumen['ventas_dolares'] + ($resumen['otros_metodos_dolares'] ?? 0)), 2) }}
                </td>
                <td class="text-center">{{ $resumen['total_ventas'] }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Saldos Finales -->
    <div class="section">
        <div class="section-title">SALDOS FINALES</div>
        <table>
            <tr>
                <th>Concepto</th>
                <th>Bolívares</th>
                <th>Dólares</th>
            </tr>
            <tr>
                <td>Saldo en Caja</td>
                <td class="text-right moneda-bs">Bs {{ number_format($resumen['saldo_actual_bs'], 2) }}</td>
                <td class="text-right moneda-usd">$ {{ number_format($resumen['saldo_actual_dolares'], 2) }}</td>
            </tr>
            @php
                $diferencia_bs = $resumen['saldo_actual_bs'] - $resumen['monto_inicial_bs'];
                $diferencia_dolares = $resumen['saldo_actual_dolares'] - $resumen['monto_inicial_dolares'];
            @endphp
            <tr class="total-row">
                <td>Diferencia (Cierre - Apertura)</td>
                <td class="text-right {{ $diferencia_bs >= 0 ? 'moneda-bs' : 'text-red-600' }}">
                    {{ $diferencia_bs >= 0 ? '+' : '' }}Bs {{ number_format($diferencia_bs, 2) }}
                </td>
                <td class="text-right {{ $diferencia_dolares >= 0 ? 'moneda-usd' : 'text-red-600' }}">
                    {{ $diferencia_dolares >= 0 ? '+' : '' }}$ {{ number_format($diferencia_dolares, 2) }}
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Lista de Ventas (si hay) -->
    @if(!empty($ventas) && count($ventas) > 0)
    <div class="section">
        <div class="section-title">DETALLE DE VENTAS ({{ count($ventas) }})</div>
        <table style="font-size: 10px;">
            <tr>
                <th>#</th>
                <th>Hora</th>
                <th>Cliente</th>
                <th>Método</th>
                <th>Total $</th>
                <th>Pagado $</th>
                <th>Deuda $</th>
            </tr>
            @foreach($ventas as $venta)
            <tr>
                <td>{{ $venta['id'] }}</td>
                <td>{{ $venta['fecha'] }}</td>
                <td>{{ Str::limit($venta['cliente'], 15) }}</td>
                <td>{{ $venta['metodo_pago'] }}</td>
                <td class="text-right">${{ number_format($venta['total_dolares'], 2) }}</td>
                <td class="text-right">${{ number_format($venta['monto_pagado_dolares'], 2) }}</td>
                <td class="text-right">
                    @if($venta['deuda_dolares'] > 0)
                        <span style="color: #e74c3c;">${{ number_format($venta['deuda_dolares'], 2) }}</span>
                    @else
                        $0.00
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif
    
    <!-- Firmas -->
    <div class="firma">
        <div class="firma-line"></div>
        <div class="text-center">Firma del Responsable</div>
        <div class="text-center">{{ $usuario }}</div>
    </div>
    
    <!-- Pie de página -->
    <div class="footer">
        <p>Sistema de Ventas - {{ config('app.name', 'Laravel') }}</p>
        <p>Documento generado automáticamente el {{ $fecha }}</p>
        <p>Este documento es el comprobante oficial de cierre de caja</p>
    </div>
</body>
</html>