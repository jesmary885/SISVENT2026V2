<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductosExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Producto::with('marca')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Código de Barras',
            'Cantidad en Stock',
            'Presentación',
            'Categoría',
            'Precio de Venta',
            'Stock Mínimo',
            'Exento',
            'Estado',
            'Marca',
            'Fecha de Creación',
            'Última Actualización'
        ];
    }

    public function map($producto): array
    {
        return [
            $producto->id,
            $producto->nombre,
            $producto->cod_barra ?? 'N/A',
            $producto->cantidad,
            $producto->presentacion ?? 'N/A',
            $producto->categoria ?? 'N/A',
            $producto->precio_venta,
            $producto->stock_minimo,
            $producto->exento ?? 'N/A',
            $producto->estado ?? 'Activo',
            $producto->marca->nombre ?? 'N/A',
            $producto->created_at->format('d/m/Y H:i'),
            $producto->updated_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]
            ],
            // Estilo para las columnas de cantidad (alertas)
            'D' => [
                'font' => ['bold' => true]
            ],
        ];
    }
}