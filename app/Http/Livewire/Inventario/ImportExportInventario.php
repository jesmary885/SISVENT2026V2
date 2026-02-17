<?php

namespace App\Http\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Imports\ProductosImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportExportInventario extends Component
{
    use WithFileUploads;

    public $open = false;
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
        'archivo.required' => 'Debe seleccionar un archivo.',
        'archivo.mimes' => 'Formato permitido: xlsx, xls o csv.',
        'archivo.max' => 'Máximo 10MB.'
    ];

    

    public function updatedArchivo()
    {
        $this->validateOnly('archivo');

        if ($this->archivo) {
            $this->nombreArchivo = $this->archivo->getClientOriginalName();
        }
    }

    public function importar()
    {
        $this->validate();

        try {

            $this->procesando = true;
            $this->errores = [];
            $this->successCount = 0;

            $import = new ProductosImport;

           Excel::import($import, $this->archivo->getRealPath());

            $this->errores = $import->getErrors();
            $this->successCount = $import->getSuccessCount();
            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();

            if (empty($this->errores)) {
                session()->flash('success',
                    "Importación completada. {$this->successCount} productos procesados ({$created} nuevos, {$updated} actualizados)."
                );
            } else {
                session()->flash('warning',
                    "Importación completada con errores. {$this->successCount} procesados, " . count($this->errores) . " con errores."
                );
                $this->mostrarErrores = true;
            }


            $this->reset(['archivo', 'nombreArchivo']);

        } catch (\Exception $e) {

         dd($e->getMessage());

            session()->flash('error', 'Error durante importación: ' . $e->getMessage());

        } finally {

            $this->procesando = false;
        }
    }

    /* =========================
        NUEVA PLANTILLA PROFESIONAL
    ========================== */

    public function descargarPlantilla()
    {
        try {

             $datos = [
                [
                    'nombre' => 'Cerveza light',
                    'codigo_de_barras' => '',
                    'marca' => 'Polar',
                    'stock_minimo' => 20,
                    'exento' => 'No',
                    'estado' => 'Activo',
                    'presentacion' => 'unidad',
                    'factor' => 1,
                    'precio' => 2,
                    'cantidad' => 480,
                ],
                [
                    'nombre' => 'Cerveza light',
                    'codigo_de_barras' => '',
                    'marca' => 'Polar',
                    'stock_minimo' => 20,
                    'exento' => 'No',
                    'estado' => 'Activo',
                    'presentacion' => 'caja',
                    'factor' => 24,
                    'precio' => 24.00,
                    'cantidad' => 20,
                ],
            ];

            return Excel::download(
                new class($datos) implements 
                    \Maatwebsite\Excel\Concerns\FromCollection,
                    \Maatwebsite\Excel\Concerns\WithHeadings,
                    \Maatwebsite\Excel\Concerns\WithColumnFormatting {

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
                            'marca',
                            'stock_minimo',
                            'exento',
                            'estado',
                            'presentacion',
                            'factor',
                            'precio',
                            'cantidad',

                        ];
                    }

                    public function columnFormats(): array
                    {
                        return [
                            'B' => '@', // código de barras como texto
                        ];
                    }
                },
                'plantilla-importacion-inventario-ultra.xlsx'
            );

        } catch (\Exception $e) {
            session()->flash('error', 'Error al generar plantilla: ' . $e->getMessage());
        }
    }

    public function close(){

          $this->open = false;
    }




    public function cancelarImportacion()
    {
        $this->reset(['archivo', 'nombreArchivo', 'errores', 'mostrarErrores']);
    }

    public function render()
    {
        return view('livewire.inventario.import-export-inventario');
    }
}
