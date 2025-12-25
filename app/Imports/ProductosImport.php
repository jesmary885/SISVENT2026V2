<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\Marca;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class ProductosImport implements ToCollection, WithHeadingRow
{
    private $errors = [];
    private $successCount = 0;
    private $updatedCount = 0;
    private $createdCount = 0;

    public function collection(Collection $rows)
    {
        $rowNumber = 2; // Empieza desde la fila 2 (después del encabezado)

        foreach ($rows as $row) {
            try {
                // Validar que la fila tenga datos
                if (empty($row['nombre']) && empty($row['codigo_de_barras'])) {
                    $this->errors[] = [
                        'fila' => $rowNumber,
                        'errores' => ['La fila está vacía o falta el nombre del producto'],
                        'datos' => $row->toArray()
                    ];
                    $rowNumber++;
                    continue;
                }

                // Preparar datos - CONVERSIÓN CORRECTA DE TIPOS
                $data = [
                    'nombre' => $this->convertToString($row['nombre'] ?? null),
                    'cod_barra' => $this->convertToString($row['codigo_de_barras'] ?? $row['cod_barra'] ?? null),
                    'cantidad' => intval($row['cantidad_en_stock'] ?? $row['cantidad'] ?? 0),
                    'presentacion' => $this->convertToString($row['presentacion'] ?? null),
                    'categoria' => $this->convertToString($row['categoria'] ?? null),
                    'precio_venta' => floatval($row['precio_de_venta'] ?? $row['precio_venta'] ?? 0),
                    'stock_minimo' => intval($row['stock_minimo'] ?? 5),
                    'exento' => in_array($row['exento'] ?? 'Si', ['Si', 'No']) ? $row['exento'] : 'Si',
                    'estado' => $this->convertToString($row['estado'] ?? 'Activo'),
                    'marca_id' => $this->getMarcaId($row['marca'] ?? null),
                ];

                // Buscar producto existente para excluirlo de la validación unique
                $productoExistente = null;
                if (!empty($data['cod_barra'])) {
                    $productoExistente = Producto::where('cod_barra', $data['cod_barra'])->first();
                }
                
                if (!$productoExistente && !empty($data['nombre'])) {
                    $productoExistente = Producto::where('nombre', $data['nombre'])->first();
                }

                // Reglas de validación
                $rules = [
                    'nombre' => 'required|string|max:255',
                    'cod_barra' => 'nullable|string|max:255',
                    'cantidad' => 'required|integer|min:0',
                    'precio_venta' => 'required|numeric|min:0',
                    'stock_minimo' => 'required|integer|min:0',
                    'exento' => 'required|in:Si,No',
                    'marca_id' => 'required|exists:marcas,id',
                ];

                // Regla unique condicional para código de barras
                if (!empty($data['cod_barra'])) {
                    if ($productoExistente && $productoExistente->cod_barra === $data['cod_barra']) {
                        // Permitir actualización del mismo producto
                        // No aplicar unique
                    } else {
                        // Validar unique solo para nuevos productos
                        $rules['cod_barra'] = 'nullable|string|max:255|unique:productos,cod_barra';
                    }
                }

                $validator = Validator::make($data, $rules, [
                    'nombre.required' => 'El nombre del producto es requerido',
                    'precio_venta.required' => 'El precio de venta es requerido',
                    'precio_venta.numeric' => 'El precio de venta debe ser un número',
                    'marca_id.required' => 'La marca es requerida',
                    'marca_id.exists' => 'La marca no existe en el sistema',
                    'cod_barra.unique' => 'El código de barras ya existe en otro producto',
                    'cod_barra.string' => 'El código de barras debe ser texto',
                ]);

                if ($validator->fails()) {
                    $this->errors[] = [
                        'fila' => $rowNumber,
                        'errores' => $validator->errors()->all(),
                        'datos' => $data
                    ];
                    $rowNumber++;
                    continue;
                }

                // Buscar o crear producto
                $producto = null;
                
                // Buscar por código de barras primero
                if (!empty($data['cod_barra'])) {
                    $producto = Producto::where('cod_barra', $data['cod_barra'])->first();
                }
                
                // Si no se encontró por código de barras, buscar por nombre
                if (!$producto && !empty($data['nombre'])) {
                    $producto = Producto::where('nombre', $data['nombre'])->first();
                }

                if ($producto) {
                    // Actualizar producto existente
                    $producto->update($data);
                    $this->updatedCount++;
                    $this->successCount++;
                } else {
                    // Crear nuevo producto
                    Producto::create($data);
                    $this->createdCount++;
                    $this->successCount++;
                }

            } catch (\Exception $e) {
                $this->errors[] = [
                    'fila' => $rowNumber,
                    'errores' => ['Error inesperado: ' . $e->getMessage()],
                    'datos' => $row->toArray()
                ];
            }

            $rowNumber++;
        }
    }

    /**
     * Convierte cualquier valor a string, manejando números grandes correctamente
     */
    private function convertToString($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_numeric($value) && $value > PHP_INT_MAX) {
            // Para números muy grandes (como códigos de barras)
            return (string)$value;
        }

        if (is_float($value)) {
            // Para evitar notación científica en floats
            return number_format($value, 2, '.', '');
        }

        return (string)$value;
    }

    private function getMarcaId($marcaNombre)
    {
        if (empty($marcaNombre)) {
            // Usar una marca por defecto
            $marca = Marca::firstOrCreate(
                ['nombre' => 'Generica'],
                ['descripcion' => 'Marca genérica para importación']
            );
            return $marca->id;
        }

        // Limpiar y estandarizar el nombre de la marca
        $marcaNombre = trim($this->convertToString($marcaNombre));
        $marcaNombre = ucfirst(strtolower($marcaNombre));

        $marca = Marca::where('nombre', $marcaNombre)->first();

        if (!$marca) {
            // Crear nueva marca si no existe
            $marca = Marca::create([
                'nombre' => $marcaNombre,
                'descripcion' => 'Creada automáticamente durante importación'
            ]);
        }

        return $marca->id;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getCreatedCount()
    {
        return $this->createdCount;
    }

    public function getUpdatedCount()
    {
        return $this->updatedCount;
    }
}