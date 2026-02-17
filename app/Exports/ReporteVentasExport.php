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
    protected $datos;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function view(): View
    {
        return view('exports.reporte-ventas', $this->datos);
    }

  
}