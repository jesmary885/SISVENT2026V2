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

    public function mount()
    {
        $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        $this->cargarEstadisticas();
    }

    public function exportarPDF()
    {
        try {
            // Primero, recargar todas las estadísticas
            $this->cargarEstadisticas();
            
            // Preparar TODOS los datos para el PDF
            $datosExportacion = [
                'fechaInicio' => $this->fechaInicio,
                'fechaFin' => $this->fechaFin,
                
                // Métricas principales REALES
                'totalVentasPeriodo' => $this->totalVentasPeriodo,
                'ingresosTotales' => $this->ingresosTotales,
                'egresosTotales' => $this->egresosTotales,
                'gananciaBruta' => $this->gananciaBruta,
                
                // Información de deudas
                'deudasPendientesTotal' => $this->deudasPendientesTotal,
                'deudasPagadasTotal' => $this->deudasPagadasTotal,
                'totalDeudas' => $this->totalDeudas,
                'detalleDeudas' => $this->detalleDeudas,
                'estadoDeudas' => $this->estadoDeudas,
                
                // Productos más vendidos (asegurar que sea colección)
                'productosMasVendidos' => collect($this->productosMasVendidos ?? []),
                
                // Métodos de pago
                'ventasPorMetodoPago' => collect($this->ventasPorMetodoPago ?? []),
                
                // Ventas por día
                'ventasPorDia' => collect($this->ventasPorDia ?? []),
                
                // Clientes que más compran
                'topClientes' => collect($this->topClientes ?? []),
            ];

            // DEBUG: Ver qué datos se están enviando
            \Log::info('Datos enviados al PDF:', $datosExportacion);

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

    private function calcularEgresos()
    {
        try {
            // Obtener todos los productos vendidos en el período con su costo
            $productosVendidos = ProductoVenta::whereBetween('producto_ventas.created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ])
            ->join('productos', 'producto_ventas.producto_id', '=', 'productos.id')
            ->select(
                'producto_ventas.cantidad',
                'productos.costo_dolares'
            )
            ->get();

            // Calcular el costo total de lo vendido
            $this->egresosTotales = $productosVendidos->sum(function($item) {
                return $item->cantidad * ($item->costo_dolares ?? 0);
            });
            
        } catch (\Exception $e) {
            \Log::error('Error calculando egresos: ' . $e->getMessage());
            $this->egresosTotales = 0;
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