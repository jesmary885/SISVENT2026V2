<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\Marca;
use App\Models\ProductoPresentacion;
use App\Models\ProductoPresentaciones;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;



class ProductosImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected $errors = [];
    protected $successCount = 0;
    protected $created = 0;
    protected $updated = 0;

    public function model(array $row)
    {
        // 🔥 Ignorar fila completamente vacía
        if (collect($row)->filter()->isEmpty()) {
            return null;
        }

        // 🔥 Ignorar si no tiene nombre
        if (empty($row['nombre'])) {
            return null;
        }

        return new Producto([
            'nombre' => $row['nombre'],
            'codigo_de_barras' => $row['codigo_de_barras'] ?? null,
            'marca' => $row['marca'] ?? null,
            'stock_minimo' => $row['stock_minimo'] ?? 0,
        ]);
    }
    
    public function collection(Collection $rows)
    {
        $rowNumber = 2;

        foreach ($rows as $row) {

            try {

                $data = [
                    'nombre' => trim($row['nombre'] ?? ''),
                    'cod_barra' => $row['codigo_de_barras'] ?? null,
                    'marca_id' => $this->getMarcaId($row['marca'] ?? null),
                    'stock_minimo' => intval($row['stock_minimo'] ?? 0),
                    'exento' => in_array($row['exento'] ?? 'Si', ['Si','No']) ? $row['exento'] : 'Si',
                    'estado' => $row['estado'] ?? 'Activo',
                    'presentacion' => strtolower(trim($row['presentacion'] ?? '')),
                    'factor' => floatval($row['factor'] ?? 0),
                    'precio' => floatval($row['precio'] ?? 0),
                    'cantidad' => floatval($row['cantidad'] ?? 0),
                ];

                $validator = Validator::make($data, [
                    'nombre' => 'required',
                    'presentacion' => 'required|in:unidad,caja',
                    'factor' => 'required|numeric|min:1',
                    'precio' => 'required|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    $this->addError($rowNumber, $validator->errors()->all(), $row);
                    $rowNumber++;
                    continue;
                }

                DB::transaction(function () use ($data) {

                    // 🧮 calcular stock_base
                    if ($data['presentacion'] == 'unidad') {
                        $stockBase = $data['cantidad'];
                    } else {
                        $stockBase = $data['cantidad'] * $data['factor'];
                    }

                    $producto = Producto::where('cod_barra', $data['cod_barra'])
                        ->orWhere('nombre', $data['nombre'])
                        ->first();

                    if ($producto) {

                        $producto->update([
                            'nombre' => $data['nombre'],
                            'cod_barra' => $data['cod_barra'],
                            'stock_base' => $stockBase,
                            'unidad_base' => 'unidad',
                            'stock_minimo' => $data['stock_minimo'],
                            'exento' => $data['exento'],
                            'estado' => $data['estado'],
                            'marca_id' => $data['marca_id'],
                        ]);

                        $this->updated++;

                    } else {

                        $producto = Producto::create([
                            'nombre' => $data['nombre'],
                            'cod_barra' => $data['cod_barra'],
                            'stock_base' => $stockBase,
                            'unidad_base' => 'unidad',
                            'stock_minimo' => $data['stock_minimo'],
                            'exento' => $data['exento'],
                            'estado' => $data['estado'],
                            'marca_id' => $data['marca_id'],
                        ]);

                        $this->created++;
                    }

                    ProductoPresentaciones::updateOrCreate(
                        [
                            'producto_id' => $producto->id,
                            'nombre' => $data['presentacion'], // SOLO unidad o caja
                        ],
                        [
                            'factor_base' => $data['factor'],
                            'precio_usd' => $data['precio'],
                            'activo' => true,
                            'cantidad_de_cajas' =>
                                $data['presentacion'] == 'caja'
                                ? $data['cantidad']
                                : null
                        ]
                    );

                    $this->successCount++;
                });

            } catch (\Exception $e) {
                $this->addError($rowNumber, [$e->getMessage()], $row);
            }

            $rowNumber++;
        }
    }

    private function getMarcaId($marcaNombre)
    {
        if (empty($marcaNombre)) {
            return Marca::firstOrCreate(['nombre' => 'Generica'])->id;
        }

        return Marca::firstOrCreate([
            'nombre' => ucfirst(strtolower(trim($marcaNombre)))
        ])->id;
    }

    private function addError($fila, $errores, $datos)
    {
        $this->errors[] = [
            'fila' => $fila,
            'errores' => $errores,
            'datos' => $datos
        ];
    }

    public function getErrors() { return $this->errors; }
    public function getSuccessCount() { return $this->successCount; }
    public function getCreatedCount() { return $this->created; }
    public function getUpdatedCount() { return $this->updated; }
}
