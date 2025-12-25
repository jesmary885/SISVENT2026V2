<?php

namespace App\Http\Livewire;

use App\Models\ProductoVenta;
use App\Models\Tasa;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Component;

class Home extends Component
{

    public $ganancia_mes_bs, $ganancia_mes_dol, $ganancia_dia_dol, $ganancia_dia_bs, $ventas_dia, $tasa_dia;

    public $productosMasVendidos = [];

    public $ventasMensuales = [];
    public $ventasAnuales = [];
    public $mesActual;
    public $añoActual;
    

    public function mount(){

        $this->productosMasVendidos = $this->obtenerProductosMasVendidos();

        $this->mesActual = Carbon::now()->month;
        $this->añoActual = Carbon::now()->year;


      

    }

    

    private function obtenerProductosMasVendidos()
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();
        
        return ProductoVenta::select(
                'producto_id',
                DB::raw('SUM(cantidad) as total_vendido'),
                DB::raw('SUM(cantidad * precio_dolares) as total_ingresos')
            )
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->with(['producto:id,nombre,precio_venta'])
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit(8)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->producto->id,
                    'nombre' => $item->producto->nombre,
                    'total_vendido' => $item->total_vendido,
                    'total_ingresos' => $item->total_ingresos,
                    'precio_venta' => $item->producto->precio_dolares,
                 
                ];
            });
    }

    public function render()
    {

        
        $mes= date('m');
        $ano= date('Y');
        $dia= date('d');

        $this->ganancia_dia_bs = Venta::whereDay('created_at', $dia)
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $ano)
            ->sum('total_bolivares');

        $this->ganancia_dia_dol = Venta::whereDay('created_at', $dia)
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $ano)
            ->sum('total_dolares');

         $this->ganancia_mes_bs = Venta::whereMonth('created_at', $mes)
            ->whereYear('created_at', $ano)
            ->sum('total_bolivares');

        $this->ganancia_mes_dol = Venta::whereMonth('created_at', $mes)
            ->whereYear('created_at', $ano)
            ->sum('total_dolares');

        $this->ventas_dia = Venta::whereDay('created_at', $dia)
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $ano)
            ->count();


        $this->tasa_dia = Tasa::find(1)->tasa_actual;

        return view('livewire.home');
    }
}
