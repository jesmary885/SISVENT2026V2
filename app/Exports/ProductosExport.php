<?php

namespace App\Exports;

use App\Models\ProductoPresentaciones;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductosExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return ProductoPresentaciones::with(['producto.marca'])
            ->where('activo', true)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Codigo de Barras',
            'Marca',
            'Stock Minimo',
            'Exento',
            'Estado',
            'Presentacion',
            'Factor',
            'Precio',
            'Cantidad'
        ];
    }

    public function map($presentacion): array
    {
        $producto = $presentacion->producto;

        if ($presentacion->nombre === 'unidad') {
            $cantidad = $producto->stock_base;
        } else {
            $cantidad = $presentacion->cantidad_de_cajas;
        }

        return [
            $producto->nombre,
            $producto->cod_barra ?? '',
            $producto->marca->nombre ?? '',
            $producto->stock_minimo,
            $producto->exento,
            $producto->estado,
            $presentacion->nombre,
            $presentacion->factor_base,
            $presentacion->precio_usd,
            $cantidad
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '16A34A']
                ]
            ]
        ];
    }
}