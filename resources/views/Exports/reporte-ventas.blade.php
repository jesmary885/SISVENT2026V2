<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2, h3 { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f3f3f3; }
        .text-right { text-align: right; }
        .section { margin-bottom: 20px; }
    </style>
</head>
<body>

<h1>Reporte General de Ventas</h1>
<p><strong>Desde:</strong> {{ $fechaInicio }}  
<strong>Hasta:</strong> {{ $fechaFin }}</p>

<div class="section">
    <h2>Resumen General</h2>
    <table>
        <tr>
            <th>Total Ventas</th>
            <th>Ingresos</th>
            <th>Egresos</th>
            <th>Ganancia Bruta</th>
        </tr>
        <tr>
            <td>{{ $totalVentasPeriodo }}</td>
            <td class="text-right">${{ number_format($ingresosTotales, 2) }}</td>
            <td class="text-right">${{ number_format($egresosTotales, 2) }}</td>
            <td class="text-right">${{ number_format($gananciaBruta, 2) }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>Ventas por Día</h2>
    <table>
        <tr>
            <th>Fecha</th>
            <th>Ventas</th>
            <th>Total</th>
        </tr>
        @foreach($ventasPorDia as $dia)

            @if($dia['ventas'] > 0)
                <tr>
                    <td>{{ $dia['fecha'] }}</td>
                    <td>{{ $dia['ventas'] }}</td>
                    <td class="text-right">${{ number_format($dia['ingresos'] ?? $dia['total'] ?? 0, 2) }}</td>
                </tr>
            @endif
        @endforeach
    </table>
</div>

<div class="section">
    <h2>Productos Más Vendidos</h2>
    <table>
        <tr>
            <th>Producto</th>
            <th>Unidades</th>
            <th>Total Generado</th>
        </tr>
        @foreach($productosMasVendidos as $producto)
        <tr>
            <td>{{ $producto->nombre ?? $producto['producto_nombre']  ?? '' }} {{ ( ucfirst($producto->presentacion_nombre)) ?? ($producto['presentacion_nombre'])  ?? '' }}</td>
            <td>{{ $producto->unidades_vendidas ?? $producto['unidades_vendidas'] ?? 0 }}</td>
            <td class="text-right">
                ${{ number_format($producto->total_generado ?? $producto['total_generado'] ?? 0, 2) }}
            </td>
        </tr>
        @endforeach
    </table>
</div>

<div class="section">
    <h2>Ventas por Método de Pago</h2>
    <table>
        <tr>
            <th>Método</th>
            <th>Cantidad</th>
            <th>Total</th>
        </tr>
        @foreach($ventasPorMetodoPago as $metodo)
        <tr>
            <td>{{ $metodo->metodo_pago ?? $metodo['metodo_pago'] ?? '' }}</td>
            <td>{{ $metodo->cantidad_ventas ?? $metodo['cantidad_ventas'] ?? 0 }}</td>
            <td class="text-right">
                ${{ number_format($metodo->total_dolares ?? $metodo['total_dolares'] ?? 0, 2) }}
            </td>
        </tr>
        @endforeach
    </table>
</div>

<div class="section">
    <h2>Top Clientes</h2>
    <table>
        <tr>
            <th>Cliente</th>
            <th>Compras</th>
            <th>Total Gastado</th>
        </tr>
        @foreach($topClientes as $cliente)
        <tr>
            <td>{{ $cliente->nombre ?? $cliente['nombre'] ?? '' }}</td>
            <td>{{ $cliente->cantidad_compras ?? $cliente['cantidad_compras'] ?? 0 }}</td>
            <td class="text-right">
                ${{ number_format($cliente->total_gastado ?? $cliente['total_gastado'] ?? 0, 2) }}
            </td>
        </tr>
        @endforeach
    </table>
</div>

</body>
</html>
