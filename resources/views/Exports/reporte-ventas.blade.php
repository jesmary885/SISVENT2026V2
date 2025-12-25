<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte Completo de Ventas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin-bottom: 5px; }
        .header p { font-size: 11px; color: #666; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 14px; font-weight: bold; background-color: #f2f2f2; padding: 5px 10px; margin-bottom: 10px; border-left: 4px solid #3498db; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table th { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 8px; text-align: left; font-weight: bold; }
        table td { border: 1px solid #dee2e6; padding: 8px; }
        .summary-table { width: 100%; margin-bottom: 20px; }
        .summary-table td { border: none; padding: 10px; vertical-align: top; text-align: center; }
        .highlight { font-size: 20px; font-weight: bold; color: #2c3e50; }
        .subtitle { font-size: 10px; color: #7f8c8d; text-transform: uppercase; margin-top: 5px; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; font-size: 10px; color: #777; text-align: center; }
        .page-break { page-break-before: always; }
        .ingresos { color: #27ae60; }
        .egresos { color: #e74c3c; }
        .ganancia { color: #2c3e50; }
        .perdida { color: #e74c3c; }
        .margen-info { margin-top: 5px; font-size: 10px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte Completo de Ventas</h1>
        <p>Período: {{ date('d/m/Y', strtotime($fechaInicio)) }} - {{ date('d/m/Y', strtotime($fechaFin)) }}</p>
        <p>Generado el: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Resumen Financiero -->
    <div class="section">
        <div class="section-title">Resumen Financiero</div>
        <table class="summary-table">
            <tr>
                <td style="width: 25%;">
                    <div class="highlight">{{ $totalVentasPeriodo ?? 0 }}</div>
                    <div class="subtitle">VENTAS</div>
                </td>
                <td style="width: 25%;">
                    <div class="highlight ingresos">${{ number_format($ingresosTotales ?? 0, 2) }}</div>
                    <div class="subtitle">INGRESOS TOTALES</div>
                </td>
                <td style="width: 25%;">
                    <div class="highlight egresos">${{ number_format($egresosTotales ?? 0, 2) }}</div>
                    <div class="subtitle">COSTO DE VENTAS</div>
                </td>
                <td style="width: 25%;">
                    @php
                        $ganancia = $gananciaBruta ?? 0;
                        $clase = $ganancia >= 0 ? 'ganancia' : 'perdida';
                        $texto = $ganancia >= 0 ? 'GANANCIA BRUTA' : 'PÉRDIDA BRUTA';
                    @endphp
                    <div class="highlight {{ $clase }}">${{ number_format(abs($ganancia), 2) }}</div>
                    <div class="subtitle">{{ $texto }}</div>
                </td>
            </tr>
        </table>
        
        @if($ingresosTotales > 0)
            @php
                $margen = ($gananciaBruta / $ingresosTotales) * 100;
                $retorno = ($egresosTotales > 0) ? ($gananciaBruta / $egresosTotales) * 100 : 0;
            @endphp
            <div class="margen-info">
                Margen: {{ number_format($margen, 1) }}% | 
                Retorno: {{ number_format($retorno, 1) }}%
            </div>
        @endif
    </div>

    <!-- Estado de Deudas -->
    <div class="section">
        <div class="section-title">Estado de Deudas</div>
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Cantidad</th>
                    <th>Monto Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Deudas Pendientes</td>
                    <td>{{ $estadoDeudas['pendientes']['cantidad'] ?? 0 }}</td>
                    <td>${{ number_format($deudasPendientesTotal ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Deudas Pagadas</td>
                    <td>{{ $estadoDeudas['pagadas']['cantidad'] ?? 0 }}</td>
                    <td>${{ number_format($deudasPagadasTotal ?? 0, 2) }}</td>
                </tr>
                <tr style="font-weight: bold;">
                    <td>TOTAL DEUDAS</td>
                    <td>{{ ($estadoDeudas['pendientes']['cantidad'] ?? 0) + ($estadoDeudas['pagadas']['cantidad'] ?? 0) }}</td>
                    <td>${{ number_format($totalDeudas ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Detalle de Deudas -->
    @if(!empty($detalleDeudas) && count($detalleDeudas) > 0)
    <div class="section">
        <div class="section-title">Detalle de Deudas</div>
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Vence / Venció</th>
                    <th>Venta #</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalleDeudas as $deuda)
                <tr>
                    <td>{{ $deuda['cliente'] ?? 'General' }}</td>
                    <td>${{ number_format($deuda['monto'] ?? 0, 2) }}</td>
                    <td>
                        @if(strtoupper($deuda['estado'] ?? '') == 'PAGADA' || strtoupper($deuda['estado'] ?? '') == 'CANCELADA')
                            <span style="background-color: #28a745; color: white; padding: 3px 8px; border-radius: 3px; font-size: 10px;">
                                {{ strtoupper($deuda['estado']) }}
                            </span>
                        @else
                            <span style="background-color: #ffc107; color: black; padding: 3px 8px; border-radius: 3px; font-size: 10px;">
                                {{ strtoupper($deuda['estado']) }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if(isset($deuda['fecha_limite']))
                            {{ date('d/m/Y', strtotime($deuda['fecha_limite'])) }}
                            @if(($deuda['dias_vencimiento'] ?? 0) >= 0)
                                En {{ $deuda['dias_vencimiento'] }} días
                            @else
                                Hace {{ abs($deuda['dias_vencimiento']) }} días
                            @endif
                        @endif
                    </td>
                    <td>#{{ $deuda['venta_id'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Productos Más Vendidos -->
    <div class="section">
        <div class="section-title">Productos Más Vendidos</div>
        @if(isset($productosMasVendidos) && !empty($productosMasVendidos) && count($productosMasVendidos) > 0)
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Unidades Vendidas</th>
                    <th>Total Generado</th>
                    <th>Precio Promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productosMasVendidos as $producto)
                <tr>
                    <td>{{ $producto->nombre ?? ($producto['nombre'] ?? 'N/A') }}</td>
                    <td>{{ $producto->unidades_vendidas ?? ($producto['unidades_vendidas'] ?? 0) }}</td>
                    <td>${{ number_format($producto->total_generado ?? ($producto['total_generado'] ?? 0), 2) }}</td>
                    <td>${{ number_format($producto->precio_promedio ?? ($producto['precio_promedio'] ?? 0), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #666; font-style: italic;">
            No hay datos de productos vendidos en este período.
        </p>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- Ventas por Método de Pago -->
    <div class="section">
        <div class="section-title">Ventas por Método de Pago</div>
        @if(isset($ventasPorMetodoPago) && !empty($ventasPorMetodoPago) && count($ventasPorMetodoPago) > 0)
        <table>
            <thead>
                <tr>
                    <th>Método de Pago</th>
                    <th>Ventas</th>
                    <th>Total</th>
                    <th>Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalVentasMetodos = 0;
                    // Calcular total dependiendo del tipo de dato
                    if(is_object($ventasPorMetodoPago) && method_exists($ventasPorMetodoPago, 'sum')) {
                        $totalVentasMetodos = $ventasPorMetodoPago->sum('total_dolares');
                    } elseif(is_array($ventasPorMetodoPago)) {
                        $totalVentasMetodos = array_sum(array_column($ventasPorMetodoPago, 'total_dolares'));
                    }
                @endphp
                @foreach($ventasPorMetodoPago as $metodo)
                @php
                    $monto = is_object($metodo) ? ($metodo->total_dolares ?? 0) : ($metodo['total_dolares'] ?? 0);
                    $metodoNombre = is_object($metodo) ? ($metodo->metodo_pago ?? 'N/A') : ($metodo['metodo_pago'] ?? 'N/A');
                    $cantidad = is_object($metodo) ? ($metodo->cantidad_ventas ?? 0) : ($metodo['cantidad_ventas'] ?? 0);
                @endphp
                <tr>
                    <td>{{ $metodoNombre }}</td>
                    <td>{{ $cantidad }}</td>
                    <td>${{ number_format($monto, 2) }}</td>
                    <td>
                        @if($totalVentasMetodos > 0)
                            {{ number_format(($monto / $totalVentasMetodos) * 100, 1) }}%
                        @else
                            0.0%
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #666; font-style: italic;">
            No hay datos de métodos de pago en este período.
        </p>
        @endif
    </div>

    <!-- Ventas por Día -->
    <div class="section">
        <div class="section-title">Ventas por Día</div>
        @if(isset($ventasPorDia) && !empty($ventasPorDia) && count($ventasPorDia) > 0)
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Ventas</th>
                    <th>Total del Día</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventasPorDia as $ventaDia)
                <tr>
                    <td>{{ $ventaDia['fecha'] ?? ($ventaDia->fecha ?? 'N/A') }}</td>
                    <td>{{ $ventaDia['ventas'] ?? ($ventaDia->ventas ?? 0) }}</td>
                    <td>${{ number_format($ventaDia['total'] ?? ($ventaDia->total ?? 0), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #666; font-style: italic;">
            No hay datos de ventas por día en este período.
        </p>
        @endif
    </div>

    <!-- Clientes que Más Compran -->
    <div class="section">
        <div class="section-title">Clientes que Más Compran</div>
        @if(isset($topClientes) && !empty($topClientes) && count($topClientes) > 0)
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Compras</th>
                    <th>Total Gastado</th>
                    <th>Deuda Actual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topClientes as $cliente)
                <tr>
                    <td>{{ $cliente->nombre ?? ($cliente['nombre'] ?? 'N/A') }}</td>
                    <td>{{ $cliente->cantidad_compras ?? ($cliente['cantidad_compras'] ?? 0) }}</td>
                    <td>${{ number_format($cliente->total_gastado ?? ($cliente['total_gastado'] ?? 0), 2) }}</td>
                    <td>
                        @php
                            $deuda = $cliente->deuda_actual ?? ($cliente['deuda_actual'] ?? 0);
                        @endphp
                        @if($deuda > 0)
                            ${{ number_format($deuda, 2) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #666; font-style: italic;">
            No hay datos de clientes en este período.
        </p>
        @endif
    </div>

    <div class="footer">
        <p><strong>Reporte Generado Automáticamente</strong></p>
        <p>Sistema de Gestión - Laravel</p>
        <p>Este reporte contiene información confidencial</p>
    </div>
</body>
</html>