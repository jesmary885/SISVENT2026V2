<?php

namespace App\Http\Livewire\Reportes;

use App\Models\Compra;
use App\Models\ProductoVenta;
use App\Models\Venta;
use App\Models\Deuda;
use App\Models\Producto;
use App\Models\Tasa;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteVentasExport;


use Carbon\CarbonPeriod;

class ReportesVentas extends Component
{
    // Filtros por fecha
    public $fechaInicio;
    public $fechaFin;
    
    // Métricas principales
    public $totalVentasPeriodo = 0;
    public $ingresosTotales = 0;
    public $egresosTotales = 0;
    public $gananciaBruta = 0;
    
    // Métricas de deudas
    public $ventasPorMetodoPago = [];
    public $productosMasVendidos = [];
    public $estadoDeudas = [];
    public $detalleDeudas = [];
    public $ventasPorDia = [];
    public $topClientes = [];
    
    // Propiedades para cálculo de deudas
    public $deudasPendientesTotal = 0;
    public $deudasPagadasTotal = 0;
    public $totalDeudas = 0;

    public $desgloseEgresos = [
    'costo_ventas' => 0,
    'compras_negocio' => 0,
    'total_compras_bolivares' => 0
    ];

    public $ventasCompletadasCount = 0;
    public $ventasPausadasCount = 0;

    public $ventasCompletadasTotalUsd = 0;
    public $ventasPausadasTotalUsd = 0;

    public $ventasCompletadasTotalVes = 0;
    public $ventasPausadasTotalVes = 0;

    public $ticketPromedioCompletadasUsd = 0;

    public $detalleCompras = [];
    public $totalComprasPeriodo = 0;

    public function mount()
    {
        $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        $this->cargarEstadisticas();
    }

    // public $desgloseEgresos = [
    //     'costo_ventas' => 0,
    //     'compras_negocio' => 0,
    //     'total_compras_bolivares' => 0
    // ];

    public function exportarPDF()
    {
        try {
            $this->cargarEstadisticas();
            
            $datosExportacion = [
                'fechaInicio' => $this->fechaInicio,
                'fechaFin' => $this->fechaFin,
                
                // Métricas principales
                'totalVentasPeriodo' => $this->totalVentasPeriodo,
                'ingresosTotales' => $this->ingresosTotales,
                'egresosTotales' => $this->egresosTotales,
                'gananciaBruta' => $this->gananciaBruta,
                'gananciaEstimada' => $this->gananciaEstimada ?? 0, // Agregar si existe
                
                // Desglose de egresos (con ambos nombres por compatibilidad)
                'desgloseEgresos' => array_merge($this->desgloseEgresos, [
                    'compras_negocio' => $this->desgloseEgresos['gasto_compras'] ?? 0
                ]),
                
                // Detalle de compras
                'detalleCompras' => $this->detalleCompras,
                'totalComprasPeriodo' => $this->totalComprasPeriodo,
                
                // Información de deudas
                'deudasPendientesTotal' => $this->deudasPendientesTotal,
                'deudasPagadasTotal' => $this->deudasPagadasTotal,
                'totalDeudas' => $this->totalDeudas,
                'detalleDeudas' => $this->detalleDeudas,
                'estadoDeudas' => $this->estadoDeudas,
                
                // Productos más vendidos
                'productosMasVendidos' => $this->productosMasVendidos,
                
                // Métodos de pago
                'ventasPorMetodoPago' => $this->ventasPorMetodoPago,
                
                // Ventas por día
                'ventasPorDia' => $this->ventasPorDia,
                
                // Clientes que más compran
                'topClientes' => $this->topClientes,
            ];

    

            $pdf = Pdf::loadView('exports.reporte-ventas', $datosExportacion)
                ->setPaper('a4', 'portrait');

            $nombreArchivo = 'reporte-completo-' . Carbon::now()->format('Y-m-d') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $nombreArchivo);

        } catch (\Exception $e) {
            \Log::error('Error generando PDF: ' . $e->getMessage());
            session()->flash('error', 'Error al generar el reporte PDF: ' . $e->getMessage());
        }
    }

    public function exportarExcel()
{
    $this->cargarEstadisticas();

    $datosExportacion = [
        'fechaInicio' => $this->fechaInicio,
        'fechaFin' => $this->fechaFin,
        'totalVentasPeriodo' => $this->totalVentasPeriodo,
        'ingresosTotales' => $this->ingresosTotales,
        'egresosTotales' => $this->egresosTotales,
        'gananciaBruta' => $this->gananciaBruta,
        'gananciaEstimada' => $this->gananciaEstimada ?? 0,
        'desgloseEgresos' => $this->desgloseEgresos,
        'detalleCompras' => $this->detalleCompras,
        'totalComprasPeriodo' => $this->totalComprasPeriodo,
        'deudasPendientesTotal' => $this->deudasPendientesTotal,
        'deudasPagadasTotal' => $this->deudasPagadasTotal,
        'totalDeudas' => $this->totalDeudas,
        'detalleDeudas' => $this->detalleDeudas,
        'estadoDeudas' => $this->estadoDeudas,
        'productosMasVendidos' => $this->productosMasVendidos,
        'ventasPorMetodoPago' => $this->ventasPorMetodoPago,
        'ventasPorDia' => $this->ventasPorDia,
        'topClientes' => $this->topClientes,
    ];

    return Excel::download(
        new ReporteVentasExport($datosExportacion),
        'reporte-ventas.xlsx'
    );
}


    private function cargarVentasPorEstado()
    {
        $base = Venta::whereBetween('created_at', [
            Carbon::parse($this->fechaInicio)->startOfDay(),
            Carbon::parse($this->fechaFin)->endOfDay()
        ]);

        $completadas = (clone $base)->where('estado', 'completada');  // ajusta el string real que uses
        $pausadas    = (clone $base)->where('estado', 'pausada');     // ajusta el string real que uses

        $this->ventasCompletadasCount = $completadas->count();
        $this->ventasPausadasCount    = $pausadas->count();

        $this->ventasCompletadasTotalUsd = (float) $completadas->sum('total_dolares');
        $this->ventasCompletadasTotalVes = (float) $completadas->sum('total_bolivares');

        $this->ventasPausadasTotalUsd = (float) $pausadas->sum('total_dolares');
        $this->ventasPausadasTotalVes = (float) $pausadas->sum('total_bolivares');

        $this->ticketPromedioCompletadasUsd = $this->ventasCompletadasCount > 0
            ? $this->ventasCompletadasTotalUsd / $this->ventasCompletadasCount
            : 0;

        // IMPORTANTE: ingresosTotales debe ser SOLO ventas completadas
        $this->totalVentasPeriodo = $this->ventasCompletadasCount;
        $this->ingresosTotales    = $this->ventasCompletadasTotalUsd;
    }


    public function cargarEstadisticas()
    {
        try {
            // 1. VENTAS TOTALES DEL PERÍODO
            $estadisticasVentas = Venta::whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])->select(
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('COALESCE(SUM(total_dolares), 0) as total_ventas_dolares'),
                DB::raw('COALESCE(AVG(total_dolares), 0) as promedio_venta')
            )->first();

            $this->cargarVentasPorEstado();
            $this->calcularEgresos();
            $this->gananciaBruta = $this->ingresosTotales - $this->egresosTotales;

            $this->totalVentasPeriodo = $estadisticasVentas->total_ventas ?? 0;
            $this->ingresosTotales = $estadisticasVentas->total_ventas_dolares ?? 0;
            
            // 2. CALCULAR EGREOS REALES (costo de lo vendido)
            $this->calcularEgresos();
            
            // 3. CALCULAR GANANCIA BRUTA REAL (ingresos - egresos)
            $this->gananciaBruta = $this->ingresosTotales - $this->egresosTotales;

            // 4. DEUDAS DEL PERÍODO
            $this->cargarEstadisticasDeudas();

            // 5. PRODUCTOS MÁS VENDIDOS
            $this->cargarProductosMasVendidos();

            // 6. VENTAS POR MÉTODO DE PAGO
            $this->cargarVentasPorMetodoPago();

            // 7. VENTAS POR DÍA
            $this->cargarVentasPorDia();

            // 8. CLIENTES QUE MÁS COMPRAN
            $this->cargarTopClientes();

        } catch (\Exception $e) {
            \Log::error('Error cargando estadísticas: ' . $e->getMessage());
            $this->resetEstadisticas();
        }
    }

    private function calcularCostoVentasPromedio()
    {
        try {
            // 1. Obtener TODAS las ventas del período
            $ventasPeriodo = ProductoVenta::whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])->get();
            
            if ($ventasPeriodo->isEmpty()) {
                \Log::info("No hay ventas en el período");
                return 0;
            }
            
            $costoTotal = 0;
            
            // 2. Agrupar ventas por producto
            $ventasPorProducto = $ventasPeriodo->groupBy('producto_presentacion_id');
            
            foreach ($ventasPorProducto as $productoId => $ventas) {
                $totalVendido = $ventas->sum('cantidad');
                \Log::info("Producto ID {$productoId}: Vendió {$totalVendido} unidades");
                
                // 3. Buscar compras de ESTE producto (hasta la fecha fin)
                $comprasProducto = Compra::where('producto_id', $productoId)
                    ->where('created_at', '<=', Carbon::parse($this->fechaFin)->endOfDay())
                    ->get();
                
                if ($comprasProducto->isEmpty()) {
                    \Log::warning("Producto {$productoId} vendido pero SIN COMPRAS registradas");
                    continue;
                }
                
                // 4. Calcular costo promedio del producto
                $totalUnidadesCompradas = $comprasProducto->sum('cantidad');
                $totalCostoCompras = $comprasProducto->sum(function($compra) {
                    return ($compra->precio_compra_dolares ?? 0) * $compra->cantidad;
                });
                
                if ($totalUnidadesCompradas > 0) {
                    $costoPromedio = $totalCostoCompras / $totalUnidadesCompradas;
                    $costoProducto = $totalVendido * $costoPromedio;
                    $costoTotal += $costoProducto;
                    
                    \Log::info("  - Costo promedio: $" . round($costoPromedio, 2));
                    \Log::info("  - Costo de ventas producto: $" . round($costoProducto, 2));
                }
            }
            
            return $costoTotal;
            
        } catch (\Exception $e) {
            \Log::error('Error calculando costo ventas: ' . $e->getMessage());
            return 0;
        }
    }

    private function calcularEgresos()
    {
        try {
            // Obtener todas las compras del período
            $comprasPeriodo = Compra::whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])->get();

            $totalComprasDolares = 0;
            $totalComprasBolivares = 0;
            $totalComprasUsdEquivalente = 0;

            // Procesar las compras y acumular los egresos
            foreach ($comprasPeriodo as $compra) {
                if ($compra->moneda_compra == 'USD') {
                    $totalComprasDolares += $compra->total_original;  // Total en USD
                    $totalComprasUsdEquivalente += $compra->total_usd_equivalente;  // Total equivalente en USD
                } elseif ($compra->moneda_compra == 'VES') {
                    $totalComprasBolivares += $compra->total_original;  // Total en VES
                    $totalComprasUsdEquivalente += $compra->total_usd_equivalente;  // Total equivalente en USD
                }
            }

            // Guardar el desglose de los egresos
            $this->desgloseEgresos = [
                'compras_dolares' => $totalComprasDolares,
                'compras_bolivares' => $totalComprasBolivares,
                'total_compras_usd_equivalente' => $totalComprasUsdEquivalente, // Total combinado en USD
            ];

            // Guardar los egresos totales
            $this->egresosTotales = $totalComprasUsdEquivalente;

            // Calcular la ganancia bruta
            $this->gananciaBruta = $this->ingresosTotales - $this->egresosTotales;

            \Log::info("=== EGRESOS ===");
            \Log::info("Compras en USD: $" . number_format($totalComprasDolares, 2));
            \Log::info("Compras en VES: Bs. " . number_format($totalComprasBolivares, 2));
            \Log::info("Total Compras en USD Equivalente: $" . number_format($totalComprasUsdEquivalente, 2));
            \Log::info("Egresos Totales: $" . number_format($this->egresosTotales, 2));

        } catch (\Exception $e) {
            \Log::error('Error calculando egresos: ' . $e->getMessage());
            $this->resetEstadisticas();
        }
    }

    private function generarVentasPorDia()
{
    $inicio = Carbon::parse($this->fechaInicio)->startOfDay();
    $fin = Carbon::parse($this->fechaFin)->endOfDay();

    // 1️⃣ Traer ventas agrupadas por día
    $ventasAgrupadas = ProductoVenta::whereBetween('created_at', [$inicio, $fin])
        ->select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('COUNT(*) as ventas'),
            DB::raw('SUM(cantidad * precio_dolares) as ingresos')
        )
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('fecha')
        ->get()
        ->keyBy('fecha');

    // 2️⃣ Construir todos los días del rango (aunque no tengan ventas)
    $ventasPorDia = [];
    $periodo = CarbonPeriod::create($inicio, $fin);

    foreach ($periodo as $fecha) {

        $fechaFormateada = $fecha->format('Y-m-d');

        $ventasPorDia[] = [
            'fecha' => $fechaFormateada,
            'ventas' => $ventasAgrupadas[$fechaFormateada]->ventas ?? 0,
            'ingresos' => $ventasAgrupadas[$fechaFormateada]->ingresos ?? 0,
        ];
    }

    return $ventasPorDia;
}



    // Agregar este método para convertir bolívares a dólares
    private function convertirBolivaresADolares($montoBolivares)
    {
        $tasa = $this->obtenerTasaCambio();
        return $tasa > 0 ? $montoBolivares / $tasa : 0;
    }




    private function obtenerTasaCambio()
    {
        return Tasa::find(1)->tasa_actual;
    }

    private function calcularCostoPromedioProducto($productoId, $fechaHasta)
    {
        try {
            // Obtener todas las compras de este producto hasta la fecha límite
            $comprasProducto = Compra::where('producto_id', $productoId)
                ->where('created_at', '<=', Carbon::parse($fechaHasta)->endOfDay())
                ->get();
            
            if ($comprasProducto->isEmpty()) {
                \Log::warning("No hay compras registradas para el producto ID: {$productoId}");
                return 0;
            }
            
            \Log::info("  - Compras encontradas para producto {$productoId}: " . $comprasProducto->count());
            
            $totalUnidadesCompradas = 0;
            $totalCostoCompras = 0;
            
            foreach ($comprasProducto as $compra) {
                // Determinar el precio unitario de esta compra
                $precioUnitario = 0;
                $cantidad = floatval($compra->cantidad);
                
                // Prioridad 1: Si tiene precio_compra_dolares
                if (!empty($compra->precio_compra_dolares) && $compra->precio_compra_dolares > 0) {
                    $precioUnitario = floatval($compra->precio_compra_dolares);
                }
                // Prioridad 2: Calcular desde total_pagado_dolares
                elseif (!empty($compra->total_pagado_dolares) && $compra->total_pagado_dolares > 0) {
                    $precioUnitario = floatval($compra->total_pagado_dolares) / $cantidad;
                }
                
                if ($precioUnitario > 0) {
                    $totalUnidadesCompradas += $cantidad;
                    $totalCostoCompras += ($precioUnitario * $cantidad);
                    
                    \Log::info("    * Compra ID {$compra->id}: {$cantidad} unidades a $" . number_format($precioUnitario, 2));
                }
            }
            
            if ($totalUnidadesCompradas > 0) {
                $costoPromedio = $totalCostoCompras / $totalUnidadesCompradas;
                \Log::info("  - Total unidades compradas: {$totalUnidadesCompradas}");
                \Log::info("  - Total costo compras: $" . number_format($totalCostoCompras, 2));
                \Log::info("  - Costo promedio calculado: $" . number_format($costoPromedio, 2));
                
                return $costoPromedio;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            \Log::error('Error calculando costo promedio para producto ' . $productoId . ': ' . $e->getMessage());
            return 0;
        }
    }

    
    private function calcularCostoVentas()
    {
        try {

            $ventasPeriodo = ProductoVenta::whereBetween('producto_ventas.created_at', [
                    Carbon::parse($this->fechaInicio)->startOfDay(),
                    Carbon::parse($this->fechaFin)->endOfDay()
                ])
                ->with([
                    'producto_presentacion.producto'
                ])
                ->get();

            if ($ventasPeriodo->isEmpty()) {
                return 0;
            }

            $costoTotal = 0;

            $ventasPorPresentacion = $ventasPeriodo->groupBy('producto_presentacion_id');

            foreach ($ventasPorPresentacion as $presentacionId => $ventas) {

                $presentacion = $ventas->first()->producto_presentacion;
                $producto = $presentacion->producto ?? null;

                $totalVendido = $ventas->sum('cantidad');

                if (!$producto) continue;

                // Convertir a unidades base
                $factor = $presentacion->factor_base ?? 1;
                $totalUnidadesBaseVendidas = $totalVendido * $factor;

                $costoPromedio = $this->calcularCostoPromedioProducto(
                    $producto->id,
                    $this->fechaFin
                );

                $costoTotal += $totalUnidadesBaseVendidas * $costoPromedio;
            }

            return $costoTotal;

        } catch (\Exception $e) {
            \Log::error('Error en calcularCostoVentas: ' . $e->getMessage());
            return 0;
        }
    }



    private function cargarEstadisticasDeudas()
    {
        try {
            // Obtener todas las deudas del período
            $deudas = Deuda::whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])
            ->with(['cliente', 'venta'])
            ->get();

            // Calcular totales
            $this->deudasPendientesTotal = $deudas->where('estado', 'pendiente')->sum('monto_dolares');
            $this->deudasPagadasTotal = $deudas->where('estado', 'pagada')->sum('monto_dolares');
            $this->totalDeudas = $deudas->sum('monto_dolares');

            // Detalle de deudas para el reporte
            $this->detalleDeudas = $deudas->map(function($deuda) {
                return [
                    'cliente' => $deuda->cliente->nombre ?? 'General',
                    'monto' => $deuda->monto_dolares,
                    'estado' => $deuda->estado,
                    'fecha_limite' => $deuda->fecha_limite,
                    'venta_id' => $deuda->venta_id,
                    'dias_vencimiento' => now()->diffInDays(Carbon::parse($deuda->fecha_limite), false)
                ];
            });

            // Resumen por estado
            $this->estadoDeudas = [
                'pendientes' => [
                    'cantidad' => $deudas->where('estado', 'pendiente')->count(),
                    'total' => $this->deudasPendientesTotal
                ],
                'pagadas' => [
                    'cantidad' => $deudas->where('estado', 'pagada')->count(),
                    'total' => $this->deudasPagadasTotal
                ]
            ];

        } catch (\Exception $e) {
            \Log::error('Error cargando deudas: ' . $e->getMessage());
            $this->deudasPendientesTotal = 0;
            $this->deudasPagadasTotal = 0;
            $this->totalDeudas = 0;
            $this->detalleDeudas = [];
            $this->estadoDeudas = [];
        }
    }

    private function cargarProductosMasVendidos()
    {
        try {
            $this->productosMasVendidos = ProductoVenta::whereBetween('producto_ventas.created_at', [
                    Carbon::parse($this->fechaInicio)->startOfDay(),
                    Carbon::parse($this->fechaFin)->endOfDay()
                ])
                ->join('producto_presentaciones', 'producto_ventas.producto_presentacion_id', '=', 'producto_presentaciones.id')
                ->join('productos', 'producto_presentaciones.producto_id', '=', 'productos.id')
                ->select(
                    'producto_presentaciones.id as presentacion_id',
                    'productos.nombre as producto_nombre',
                    'producto_presentaciones.nombre as presentacion_nombre',
                    DB::raw('SUM(producto_ventas.cantidad) as unidades_vendidas'),
                    DB::raw('SUM(producto_ventas.cantidad * producto_ventas.precio_dolares) as total_generado'),
                    DB::raw('AVG(producto_ventas.precio_dolares) as precio_promedio')
                )
                ->groupBy(
                    'producto_presentaciones.id',
                    'productos.nombre',
                    'producto_presentaciones.nombre'
                )
                ->orderByDesc('unidades_vendidas')
                ->limit(10)
                ->get();

        } catch (\Exception $e) {
            \Log::error('Error cargando productos más vendidos: ' . $e->getMessage());
            $this->productosMasVendidos = collect([]);
        }
    }


    private function cargarVentasPorMetodoPago()
    {
        try {
            $this->ventasPorMetodoPago = Venta::whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])
            ->select(
                'metodo_pago',
                DB::raw('COUNT(*) as cantidad_ventas'),
                DB::raw('COALESCE(SUM(total_dolares), 0) as total_dolares')
            )
            ->groupBy('metodo_pago')
            ->orderByDesc('total_dolares')
            ->get();

            if (!$this->ventasPorMetodoPago) {
                $this->ventasPorMetodoPago = collect([]);
            }

        } catch (\Exception $e) {
            \Log::error('Error cargando ventas por método de pago: ' . $e->getMessage());
            $this->ventasPorMetodoPago = collect([]);
        }
    }

    private function cargarVentasPorDia()
    {
    $inicio = Carbon::parse($this->fechaInicio)->startOfDay();
    $fin = Carbon::parse($this->fechaFin)->endOfDay();

   
    $ventasAgrupadas = ProductoVenta::whereBetween('created_at', [$inicio, $fin])
        ->select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('COUNT(*) as ventas'),
            DB::raw('SUM(cantidad * precio_dolares) as ingresos')
        )
        ->groupBy(DB::raw('DATE(created_at)'))
        ->get()
        ->keyBy('fecha');

    

    $ventasPorDia = [];
    $periodo = CarbonPeriod::create($inicio, $fin);

    foreach ($periodo as $fecha) {

        $fechaKey = $fecha->format('Y-m-d');

        $ventasPorDia[] = [
            'fecha' => $fechaKey,
            'ventas' => isset($ventasAgrupadas[$fechaKey])
                ? (int) $ventasAgrupadas[$fechaKey]->ventas
                : 0,
            'ingresos' => isset($ventasAgrupadas[$fechaKey])
                ? (float) $ventasAgrupadas[$fechaKey]->ingresos
                : 0,
        ];
    }

    

 

    $this->ventasPorDia = $ventasPorDia;
}


    private function cargarTopClientes()
    {
        try {
            $this->topClientes = Venta::whereBetween('ventas.created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])
            ->join('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->select(
                'clientes.nombre',
                'clientes.telefono',
                DB::raw('COUNT(ventas.id) as cantidad_compras'),
                DB::raw('COALESCE(SUM(ventas.total_dolares), 0) as total_gastado'),
                DB::raw('COALESCE(SUM(ventas.deuda_dolares), 0) as deuda_actual')
            )
            ->groupBy('clientes.id', 'clientes.nombre', 'clientes.telefono')
            ->orderByDesc('total_gastado')
            ->limit(5)
            ->get();

            if (!$this->topClientes) {
                $this->topClientes = collect([]);
            }

        } catch (\Exception $e) {
            \Log::error('Error cargando top clientes: ' . $e->getMessage());
            $this->topClientes = collect([]);
        }
    }

    private function resetEstadisticas()
    {
        $this->totalVentasPeriodo = 0;
        $this->ingresosTotales = 0;
        $this->egresosTotales = 0;
        $this->gananciaBruta = 0;
        $this->deudasPendientesTotal = 0;
        $this->deudasPagadasTotal = 0;
        $this->totalDeudas = 0;
        $this->productosMasVendidos = collect([]);
        $this->ventasPorMetodoPago = collect([]);
        $this->ventasPorDia = collect([]);
        $this->topClientes = collect([]);
        $this->estadoDeudas = [];
        $this->detalleDeudas = [];
        
        // RESETEAR LAS NUEVAS PROPIEDADES
        $this->desgloseEgresos = [
            'costo_ventas' => 0,
            'gasto_compras' => 0,
            'total_compras_bolivares' => 0
        ];
        $this->detalleCompras = collect([]);
        $this->totalComprasPeriodo = 0;
    }

    public function updatedFechaInicio()
    {
        $this->cargarEstadisticas();
    }

    public function updatedFechaFin()
    {
        $this->cargarEstadisticas();
    }

    public function render()
    {
        return view('livewire.reportes.reportes-ventas');
    }
}