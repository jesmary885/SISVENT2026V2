<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Exports\ProductosExport;
use App\Imports\ProductosImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ImportExportInventario extends Component
{

   use WithFileUploads;
   
    protected $listeners = ['render'];

    public $open = false;

   

    public function close(){

        $this->open = false;

    }

    public $archivo;
    public $procesando = false;
    public $errores = [];
    public $successCount = 0;
    public $mostrarErrores = false;
    public $nombreArchivo = '';

    protected $rules = [
        'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240'
    ];

    protected $messages = [
        'archivo.required' => 'Debe seleccionar un archivo para importar.',
        'archivo.file' => 'El archivo debe ser válido.',
        'archivo.mimes' => 'El archivo debe ser de tipo: xlsx, xls, csv.',
        'archivo.max' => 'El archivo no debe pesar más de 10MB.'
    ];

    public function updatedArchivo()
    {
        $this->validateOnly('archivo');
        
        if ($this->archivo) {
            $this->nombreArchivo = $this->archivo->getClientOriginalName();
            $this->resetErrorBag();
            
            // Opcional: validar tamaño adicional
            if ($this->archivo->getSize() > 10 * 1024 * 1024) { // 10MB en bytes
                $this->addError('archivo', 'El archivo es demasiado grande. Máximo 10MB permitido.');
                $this->archivo = null;
                $this->nombreArchivo = '';
            }
        }
    }

    public function exportar()
    {
        try {
            $nombreArchivo = 'inventario-productos-' . date('Y-m-d-H-i') . '.xlsx';
            
            return Excel::download(new ProductosExport, $nombreArchivo);
            
        } catch (\Exception $e) {
            $this->addError('exportar', 'Error al exportar: ' . $e->getMessage());
        }
    }

    public function importar()
    {
        $this->validate();

        if (!$this->archivo) {
            $this->addError('archivo', 'Debe seleccionar un archivo para importar.');
            return;
        }

        try {
            $this->procesando = true;
            $this->errores = [];
            $this->successCount = 0;

            $import = new ProductosImport;
            
            Excel::import($import, $this->archivo);

            $this->errores = $import->getErrors();
            $this->successCount = $import->getSuccessCount();
            $createdCount = $import->getCreatedCount();
            $updatedCount = $import->getUpdatedCount();

            if (empty($this->errores)) {
                session()->flash('success', "✅ Importación completada exitosamente. {$this->successCount} productos procesados ({$createdCount} nuevos, {$updatedCount} actualizados).");
            } else {
                session()->flash('warning', "⚠️ Importación completada con errores. {$this->successCount} productos procesados ({$createdCount} nuevos, {$updatedCount} actualizados), " . count($this->errores) . " con errores.");
                $this->mostrarErrores = true;
            }

            // Limpiar después de importar
            $this->reset(['archivo', 'nombreArchivo']);

        } catch (\Exception $e) {
            session()->flash('error', '❌ Error durante la importación: ' . $e->getMessage());
        } finally {
            $this->procesando = false;
        }
    }

    public function descargarPlantilla()
        {
            try {
                // Crear plantilla básica con datos de ejemplo
                $datosEjemplo = [
                    [
                        'nombre' => 'Producto Ejemplo 1',
                        'codigo_de_barras' => '="7501234567890"', // Fuerza texto en Excel
                        'cantidad_en_stock' => 50,
                        'presentacion' => 'Unidad',
                        'categoria' => 'Electrónicos',
                        'precio_de_venta' => 299.99,
                        'stock_minimo' => 10,
                        'exento' => 'Si',
                        'estado' => 'Activo',
                        'marca' => 'Samsung'
                    ],
                    [
                        'nombre' => 'Producto Ejemplo 2', 
                        'codigo_de_barras' => '="7501234567891"', // Fuerza texto en Excel
                        'cantidad_en_stock' => 25,
                        'presentacion' => 'Paquete',
                        'categoria' => 'Hogar',
                        'precio_de_venta' => 49.99,
                        'stock_minimo' => 5,
                        'exento' => 'No',
                        'estado' => 'Activo',
                        'marca' => 'Generica'
                    ]
                ];

                return Excel::download(new class($datosEjemplo) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithColumnFormatting {
                    private $datos;

                    public function __construct($datos)
                    {
                        $this->datos = collect($datos);
                    }

                    public function collection()
                    {
                        return $this->datos;
                    }

                    public function headings(): array
                    {
                        return [
                            'nombre',
                            'codigo_de_barras',
                            'cantidad_en_stock',
                            'presentacion',
                            'categoria',
                            'precio_de_venta',
                            'stock_minimo',
                            'exento',
                            'estado',
                            'marca'
                        ];
                    }

                    public function columnFormats(): array
                    {
                        return [
                            'B' => '@', // Formato texto para código de barras
                        ];
                    }
                }, 'plantilla-importacion-inventario.xlsx');

            } catch (\Exception $e) {
                session()->flash('error', 'Error al descargar plantilla: ' . $e->getMessage());
            }
        }

    public function cancelarImportacion()
    {
        $this->reset(['archivo', 'nombreArchivo', 'errores', 'mostrarErrores']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.inventario.import-export-inventario');
    }
}
