<?php

namespace App\Exports;

use App\Models\Venta;
use App\Models\ProductoVenta;
use App\Models\Deuda;
use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReporteVentasExport implements FromView
{
    protected $fechaInicio;
    protected $fechaFin;
    
    // Métricas principales
    protected $totalVentasPeriodo;
    protected $ingresosTotales;
    protected $egresosTotales; // Costo de las ventas + Compras del negocio
    protected $gananciaBruta; // Ingresos - Egresos
    protected $gananciaEstimada;
    
    // Desglose de egresos
    protected $desgloseEgresos;
    
    // NUEVO: Detalle de compras
    protected $detalleCompras;
    protected $totalComprasPeriodo;
    
    // Métricas de deudas
    protected $deudasPendientesTotal;
    protected $deudasPagadasTotal;
    protected $totalDeudas;
    protected $detalleDeudas;
    protected $estadoDeudas;
    
    // Otras métricas
    protected $productosMasVendidos;
    protected $ventasPorMetodoPago;
    protected $ventasPorDia;
    protected $topClientes;

    public function __construct($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        
        // Inicializar desglose de egresos
        $this->desgloseEgresos = [
            'costo_ventas' => 0,
            'compras_negocio' => 0,
            'total_compras_bolivares' => 0
        ];
        
        // Inicializar detalle de compras
        $this->detalleCompras = collect([]);
        $this->totalComprasPeriodo = 0;
        
        // Calcular todas las estadísticas al instanciar la clase
        $this->calcularEstadisticas();
    }

    private function calcularEstadisticas()
    {
        // 1. VENTAS TOTALES DEL PERÍODO
        $estadisticasVentas = Venta::whereBetween('created_at', [
            Carbon::parse($this->fechaInicio)->startOfDay(),
            Carbon::parse($this->fechaFin)->endOfDay()
        ])->select(
            DB::raw('COUNT(*) as total_ventas'),
            DB::raw('COALESCE(SUM(total_dolares), 0) as total_ventas_dolares'),
            DB::raw('COALESCE(AVG(total_dolares), 0) as promedio_venta')
        )->first();

        $this->totalVentasPeriodo = $estadisticasVentas->total_ventas;
        $this->ingresosTotales = $estadisticasVentas->total_ventas_dolares;
        
        // 2. CALCULAR EGREOS (COSTO DE LO VENDIDO + COMPRAS DEL NEGOCIO)
        $this->calcularEgresos();
        
        // 3. NUEVO: OBTENER DETALLE DE COMPRAS
        $this->calcularDetalleCompras();
        
        // 4. GANANCIA BRUTA REAL (Ingresos - Egresos)
        $this->gananciaBruta = $this->ingresosTotales - $this->egresosTotales;
        
        // 5. GANANCIA ESTIMADA (por si quieres mantenerla)
        $this->gananciaEstimada = $this->ingresosTotales * 0.6;

        // 6. DEUDAS DEL PERÍODO
        $this->calcularDeudas();

        // 7. PRODUCTOS MÁS VENDIDOS
        $this->calcularProductosMasVendidos();

        // 8. VENTAS POR MÉTODO DE PAGO
        $this->calcularVentasPorMetodoPago();

        // 9. VENTAS POR DÍA
        $this->calcularVentasPorDia();

        // 10. CLIENTES QUE MÁS COMPRAN
        $this->calcularTopClientes();
    }

    // MÉTODO ACTUALIZADO: Calcular egresos (costo de lo vendido + compras del negocio)
    private function calcularEgresos()
    {
        try {
            // A) COSTO DE LO VENDIDO
            $costoVentas = 0;
            
            try {
                $productosVendidos = ProductoVenta::whereBetween('producto_ventas.created_at', [
                    Carbon::parse($this->fechaInicio)->startOfDay(),
                    Carbon::parse($this->fechaFin)->endOfDay()
                ])
                ->with('producto')
                ->get();

                $costoVentas = $productosVendidos->sum(function($item) {
                    if ($item->producto) {
                        $costo = $item->producto->costo_dolares ?? 
                                $item->producto->costo ?? 
                                $item->producto->precio_costo ?? 
                                $item->producto->precio_compra ?? 0;
                        return $item->cantidad * $costo;
                    }
                    return 0;
                });

            } catch (\Exception $e) {
                $costoVentas = 0;
            }

            // B) COMPRAS DEL NEGOCIO - CORREGIDO
            $comprasNegocio = Compra::whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])->get();

            // Suma manejando NULLs
            $totalComprasDolares = $comprasNegocio->sum(function($compra) {
                return $compra->total_pagado_dolares ?? 0;
            });
            
            $totalComprasBolivares = $comprasNegocio->sum(function($compra) {
                return $compra->total_pagado_bolivares ?? 0;
            });

            // Calcular alternativo si total_pagado es 0 o NULL
            $totalAlternativoDolares = $comprasNegocio->sum(function($compra) {
                if (($compra->total_pagado_dolares ?? 0) > 0) {
                    return $compra->total_pagado_dolares;
                }
                return ($compra->precio_compra_dolares ?? 0) * $compra->cantidad;
            });

            // Usar el mayor valor
            $totalComprasDolares = max($totalComprasDolares, $totalAlternativoDolares);

            // C) TOTAL DE EGRESOS
            $this->egresosTotales = $costoVentas + $totalComprasDolares;
            
            // D) Almacenar desglose
            $this->desgloseEgresos = [
                'costo_ventas' => $costoVentas,
                'compras_negocio' => $totalComprasDolares,
                'total_compras_bolivares' => $totalComprasBolivares
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error calculando egresos en ReporteVentasExport: ' . $e->getMessage());
            $this->egresosTotales = 0;
            $this->desgloseEgresos = [
                'costo_ventas' => 0,
                'compras_negocio' => 0,
                'total_compras_bolivares' => 0
            ];
        }
    }

    // NUEVO MÉTODO: Obtener detalle de compras realizadas
    private function calcularDetalleCompras()
    {
        try {
            $this->detalleCompras = Compra::whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])
            ->with(['producto', 'proveedor', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

            $this->totalComprasPeriodo = $this->detalleCompras->count();
            
        } catch (\Exception $e) {
            \Log::error('Error obteniendo detalle de compras: ' . $e->getMessage());
            $this->detalleCompras = collect([]);
            $this->totalComprasPeriodo = 0;
        }
    }
    private function calcularDeudas()
    {
        $deudas = Deuda::whereBetween('created_at', [
            Carbon::parse($this->fechaInicio)->startOfDay(),
            Carbon::parse($this->fechaFin)->endOfDay()
        ])
        ->with(['cliente', 'venta'])
        ->get();

        $this->deudasPendientesTotal = $deudas->where('estado', 'pendiente')->sum('monto_dolares');
        $this->deudasPagadasTotal = $deudas->where('estado', 'pagada')->sum('monto_dolares');
        $this->totalDeudas = $deudas->sum('monto_dolares');

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

        // CALCULAR $estadoDeudas también
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
    }

    private function calcularProductosMasVendidos()
    {
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

        // Si no hay productos, crea un array vacío con estructura correcta
        if ($this->productosMasVendidos->isEmpty()) {
            $this->productosMasVendidos = collect([]);
        }
    }

    private function calcularVentasPorMetodoPago()
    {
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

        if ($this->ventasPorMetodoPago->isEmpty()) {
            $this->ventasPorMetodoPago = collect([]);
        }
    }

    private function calcularVentasPorDia()
    {
        $this->ventasPorDia = Venta::whereBetween('created_at', [
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
        ->get()
        ->map(function($item) {
            return [
                'fecha' => Carbon::parse($item->fecha)->format('d/m'),
                'ventas' => $item->cantidad_ventas,
                'total' => $item->total_dolares
            ];
        });

        if ($this->ventasPorDia->isEmpty()) {
            $this->ventasPorDia = collect([]);
        }
    }

    private function calcularTopClientes()
    {
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

        if ($this->topClientes->isEmpty()) {
            $this->topClientes = collect([]);
        }
    }

    public function view(): View
    {
        return view('exports.reporte-ventas', [
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            
            // Métricas principales
            'totalVentasPeriodo' => $this->totalVentasPeriodo,
            'ingresosTotales' => $this->ingresosTotales,
            'egresosTotales' => $this->egresosTotales,
            'gananciaBruta' => $this->gananciaBruta,
            'gananciaEstimada' => $this->gananciaEstimada,
            
            // Desglose de egresos
            'desgloseEgresos' => $this->desgloseEgresos,
            
            // NUEVO: Detalle de compras
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
        ]);
    }
}