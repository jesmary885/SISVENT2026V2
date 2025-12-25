<?php

use App\Http\Controllers\FacturaController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});




Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () 
{


     Route::get('/home',[GeneralController::class,'home'])->name('home');

    Route::get('Inventario',[GeneralController::class,'inventario_index'])->name('inventario_index');
    Route::get('Ventas',[GeneralController::class,'ventas_index'])->name('ventas_index');
    Route::get('Compras',[GeneralController::class,'compras_index'])->name('compras_index');
    Route::get('Proveedores',[GeneralController::class,'proveedores_index'])->name('proveedores_index');
    Route::get('cajas',[GeneralController::class,'cajas_index'])->name('cajas_index');
    Route::get('Administracion',[GeneralController::class,'administracion'])->name('administracion');
    Route::get('Configuracion',[GeneralController::class,'configuracion'])->name('configuracion');
    Route::get('Reportes',[GeneralController::class,'reportes'])->name('reportes');
    Route::get('Caja',[GeneralController::class,'caja'])->name('caja');
    Route::get('Cambiar-credenciales',[GeneralController::class,'cambiarCredenciales'])->name('perfil.credenciales');

    Route::get('/ticket/{id}', [TicketController::class, 'generarTicket'])->name('ticket.pdf');
    Route::get('/ticket-preview/{venta}', [TicketController::class, 'vistaPrevia'])->name('ticket.preview');
    Route::get('/factura/{id}', [FacturaController::class, 'generarFactura'])->name('factura.pdf');
    Route::get('/factura-preview/{venta}', [FacturaController::class, 'vistaPrevia'])->name('factura.preview');

    // Gestión de deudas
    Route::get('/deudas', \App\Http\Livewire\Ventas\DeudasIndex::class)->name('deudas.index');

    Route::get('/inventario/import-export', function () {
        return view('inventario-import-export');
    })->name('inventario.import-export');



});
