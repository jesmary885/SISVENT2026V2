<?php

namespace App\Http\Livewire\Reportes;

use App\Models\Compra;
use App\Models\ProductoVenta;
use App\Models\Venta;
use App\Models\Deuda;
use App\Models\Producto;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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

            \Log::info("PDF - Datos a exportar:");
            \Log::info("- Productos más vendidos: " . ($this->productosMasVendidos->count() ?? 0));
            \Log::info("- Ventas por método pago: " . ($this->ventasPorMetodoPago->count() ?? 0));
            \Log::info("- Ventas por día: " . ($this->ventasPorDia->count() ?? 0));
            \Log::info("- Top clientes: " . ($this->topClientes->count() ?? 0));

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
            $ventasPorProducto = $ventasPeriodo->groupBy('producto_id');
            
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
            \Log::info("=== CALCULAR EGRESOS - ESTRUCTURA CLARA ===");
            
            // A) CALCULAR COSTO DE VENTAS (lo que realmente te costó vender)
            $costoVentas = $this->calcularCostoVentas();
            \Log::info("1. Costo de Ventas (costo real de lo vendido): $" . number_format($costoVentas, 2));
            
            // B) CALCULAR COMPRAS DEL PERÍODO (inversiones en inventario)
            $comprasPeriodo = Compra::whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])->get();

            $totalComprasDolares = 0;
            $totalComprasBolivares = 0;
            
            foreach ($comprasPeriodo as $compra) {
                // Para dólares
                if (!empty($compra->total_pagado_dolares) && $compra->total_pagado_dolares > 0) {
                    $totalComprasDolares += $compra->total_pagado_dolares;
                } elseif (!empty($compra->precio_compra_dolares) && $compra->precio_compra_dolares > 0) {
                    $totalComprasDolares += ($compra->precio_compra_dolares * $compra->cantidad);
                }
                
                // Para bolívares
                if (!empty($compra->total_pagado_bolivares) && $compra->total_pagado_bolivares > 0) {
                    $totalComprasBolivares += $compra->total_pagado_bolivares;
                } elseif (!empty($compra->precio_compra_bolivares) && $compra->precio_compra_bolivares > 0) {
                    $totalComprasBolivares += ($compra->precio_compra_bolivares * $compra->cantidad);
                }
            }
            
            \Log::info("2. Compras nuevas en el período: " . $comprasPeriodo->count() . " compras");
            \Log::info("   - Total en dólares: $" . number_format($totalComprasDolares, 2));
            \Log::info("   - Total en bolívares: Bs. " . number_format($totalComprasBolivares, 2));
            
            // C) OBTENER DETALLE DE COMPRAS PARA MOSTRAR
            $this->detalleCompras = Compra::whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])
            ->with(['producto', 'proveedor', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
            
            $this->totalComprasPeriodo = $this->detalleCompras->count();
            
            // D) CALCULAR TOTALES
            // LOS EGRESOS SON EL COSTO DE LO VENDIDO (no las compras nuevas)
            $this->egresosTotales = $costoVentas;
            
            // GANANCIA BRUTA = VENTAS - COSTO DE VENTAS
            $this->gananciaBruta = $this->ingresosTotales - $costoVentas;
            
            // E) ALMACENAR TODO EN DESGLOSE
            $this->desgloseEgresos = [
                'costo_ventas' => $costoVentas,
                'compras_negocio' => $totalComprasDolares,
                'gasto_compras' => $totalComprasDolares, // alias para la vista
                'total_compras_bolivares' => $totalComprasBolivares
            ];
            
            \Log::info("=== RESUMEN FINAL ===");
            \Log::info("Ingresos Totales (Ventas): $" . number_format($this->ingresosTotales, 2));
            \Log::info("Costo de Ventas: $" . number_format($costoVentas, 2));
            \Log::info("Compras nuevas (inversión): $" . number_format($totalComprasDolares, 2));
            \Log::info("Egresos Totales (solo costo ventas): $" . number_format($this->egresosTotales, 2));
            \Log::info("Ganancia Bruta: $" . number_format($this->gananciaBruta, 2));
            \Log::info("Margen Bruto: " . ($this->ingresosTotales > 0 ? 
                number_format((($this->ingresosTotales - $costoVentas) / $this->ingresosTotales) * 100, 1) . '%' : '0%'));
            
        } catch (\Exception $e) {
            \Log::error('Error en calcularEgresos: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            // Valores por defecto en caso de error
            $this->egresosTotales = 0;
            $this->gananciaBruta = $this->ingresosTotales;
            $this->desgloseEgresos = [
                'costo_ventas' => 0,
                'compras_negocio' => 0,
                'gasto_compras' => 0,
                'total_compras_bolivares' => 0
            ];
            $this->detalleCompras = collect([]);
            $this->totalComprasPeriodo = 0;
        }
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
            \Log::info("=== CALCULAR COSTO DE VENTAS - BASADO EN COMPRAS ===");
            
            // 1. Obtener todas las ventas del período con productos
            $ventasPeriodo = ProductoVenta::whereBetween('producto_ventas.created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])
            ->with(['producto'])
            ->get();
            
            if ($ventasPeriodo->isEmpty()) {
                \Log::info("No hay ventas en el período");
                return 0;
            }
            
            \Log::info("Ventas encontradas: " . $ventasPeriodo->count());
            
            $costoTotal = 0;
            
            // 2. Agrupar ventas por producto
            $ventasPorProducto = $ventasPeriodo->groupBy('producto_id');
            
            foreach ($ventasPorProducto as $productoId => $ventas) {
                $producto = $ventas->first()->producto ?? null;
                $totalVendido = $ventas->sum('cantidad');
                
                if (!$producto) {
                    \Log::warning("⚠️ Producto ID {$productoId} no encontrado");
                    continue;
                }
                
                \Log::info("Producto: {$producto->nombre} (ID: {$productoId}) - Vendió {$totalVendido} unidades");
                
                // 3. Calcular el costo promedio de este producto basado en las compras
                $costoPromedio = $this->calcularCostoPromedioProducto($productoId, $this->fechaFin);
                
                if ($costoPromedio <= 0) {
                    \Log::warning("⚠️ No se pudo determinar costo para {$producto->nombre}, usando estimado");
                    // Estimar costo como 60% del precio de venta si no hay compras
                    $costoPromedio = floatval($producto->precio_venta) * 0.6;
                }
                
                \Log::info("  - Costo promedio: $" . number_format($costoPromedio, 2));
                
                // 4. Calcular costo total de lo vendido
                $costoProducto = $totalVendido * $costoPromedio;
                $costoTotal += $costoProducto;
                
                \Log::info("  - Costo total del producto: $" . number_format($costoProducto, 2));
                \Log::info("  - Costo acumulado total: $" . number_format($costoTotal, 2));
            }
            
            \Log::info("Costo TOTAL de ventas: $" . number_format($costoTotal, 2));
            return $costoTotal;
            
        } catch (\Exception $e) {
            \Log::error('Error en calcularCostoVentas: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
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
            ->join('productos', 'producto_ventas.producto_id', '=', 'productos.id')
            ->select(
                'productos.nombre',
                DB::raw('SUM(producto_ventas.cantidad) as unidades_vendidas'),
                DB::raw('SUM(producto_ventas.cantidad * producto_ventas.precio_dolares) as total_generado'),
                DB::raw('AVG(producto_ventas.precio_dolares) as precio_promedio')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('unidades_vendidas')
            ->limit(10)
            ->get();

            // Si no hay productos, crear array vacío
            if (!$this->productosMasVendidos) {
                $this->productosMasVendidos = collect([]);
            }

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
        try {
            $ventasDia = Venta::whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])
            ->select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as cantidad_ventas'),
                DB::raw('COALESCE(SUM(total_dolares), 0) as total_dolares')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

            $this->ventasPorDia = $ventasDia->map(function($item) {
                return [
                    'fecha' => Carbon::parse($item->fecha)->format('d/m'),
                    'ventas' => $item->cantidad_ventas,
                    'total' => $item->total_dolares
                ];
            });

            if (!$this->ventasPorDia) {
                $this->ventasPorDia = collect([]);
            }

        } catch (\Exception $e) {
            \Log::error('Error cargando ventas por día: ' . $e->getMessage());
            $this->ventasPorDia = collect([]);
        }
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