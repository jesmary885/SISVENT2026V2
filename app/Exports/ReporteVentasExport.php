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
    protected $egresosTotales; // NUEVO: Costo de las ventas
    protected $gananciaBruta; // NUEVO: Ingresos - Egresos
    protected $gananciaEstimada;
    
    // Métricas de deudas
    protected $deudasPendientesTotal;
    protected $deudasPagadasTotal;
    protected $totalDeudas;
    protected $detalleDeudas;
    protected $estadoDeudas; // NUEVO: Agregar esta variable
    
    // Otras métricas
    protected $productosMasVendidos;
    protected $ventasPorMetodoPago;
    protected $ventasPorDia;
    protected $topClientes;

    public function __construct($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        
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
        
        // 2. CALCULAR EGREOS (COSTO DE LO VENDIDO) - NUEVO
        $this->calcularEgresos();
        
        // 3. GANANCIA BRUTA REAL (Ingresos - Egresos)
        $this->gananciaBruta = $this->ingresosTotales - $this->egresosTotales;
        
        // 4. GANANCIA ESTIMADA (por si quieres mantenerla)
        $this->gananciaEstimada = $this->ingresosTotales * 0.6;

        // 5. DEUDAS DEL PERÍODO
        $this->calcularDeudas();

        // 6. PRODUCTOS MÁS VENDIDOS
        $this->calcularProductosMasVendidos();

        // 7. VENTAS POR MÉTODO DE PAGO
        $this->calcularVentasPorMetodoPago();

        // 8. VENTAS POR DÍA
        $this->calcularVentasPorDia();

        // 9. CLIENTES QUE MÁS COMPRAN
        $this->calcularTopClientes();
    }

    // NUEVO MÉTODO: Calcular egresos (costo de lo vendido)
    private function calcularEgresos()
    {
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
            'egresosTotales' => $this->egresosTotales, // NUEVO
            'gananciaBruta' => $this->gananciaBruta, // NUEVO
            'gananciaEstimada' => $this->gananciaEstimada,
            
            // Información de deudas
            'deudasPendientesTotal' => $this->deudasPendientesTotal,
            'deudasPagadasTotal' => $this->deudasPagadasTotal,
            'totalDeudas' => $this->totalDeudas,
            'detalleDeudas' => $this->detalleDeudas,
            'estadoDeudas' => $this->estadoDeudas, // NUEVO: Agregar esta línea
            
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