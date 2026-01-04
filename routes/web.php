<?php

use App\Http\Controllers\FacturaController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\TicketController;
use App\Http\Livewire\Roles\RoleForm;
use App\Http\Livewire\Roles\RoleIndex;
use App\Http\Livewire\User\UserRoleManager;
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


     Route::get('/home',[GeneralController::class,'home'])->name('home')->middleware('permission:Cajero');

    Route::get('Inventario',[GeneralController::class,'inventario_index'])->name('inventario_index')->middleware('permission:Cajero');
    Route::get('Ventas',[GeneralController::class,'ventas_index'])->name('ventas_index')->middleware('permission:Cajero');
    Route::get('Compras',[GeneralController::class,'compras_index'])->name('compras_index')->middleware('permission:Administrador');
    Route::get('Proveedores',[GeneralController::class,'proveedores_index'])->name('proveedores_index')->middleware('permission:Administrador');
    Route::get('cajas',[GeneralController::class,'cajas_index'])->name('cajas_index')->middleware('permission:Administrador');
    Route::get('Administracion',[GeneralController::class,'administracion'])->name('administracion')->middleware('permission:Administrador');
    Route::get('Configuracion',[GeneralController::class,'configuracion'])->name('configuracion')->middleware('permission:Administrador');
    Route::get('Reportes',[GeneralController::class,'reportes'])->name('reportes')->middleware('permission:Administrador');
    Route::get('Caja',[GeneralController::class,'caja'])->name('caja')->middleware('permission:Cajero');
    Route::get('Cambiar-credenciales',[GeneralController::class,'cambiarCredenciales'])->name('perfil.credenciales')->middleware('permission:Administrador');

    Route::get('/ticket/{venta}', [TicketController::class, 'generarTicket'])->name('ticket.pdf')->middleware('permission:Cajero');
    Route::get('/ticket-preview/{venta}', [TicketController::class, 'vistaPrevia'])->name('ticket.preview')->middleware('permission:Cajero');
    Route::get('/factura/{venta}', [FacturaController::class, 'generarFactura'])->name('factura.pdf')->middleware('permission:Cajero');
    Route::get('/factura-preview/{venta}', [FacturaController::class, 'vistaPrevia'])->name('factura.preview')->middleware('permission:Cajero');

     // Roles
    Route::get('/roles', RoleIndex::class)->name('roles.index')->middleware('permission:Administrador');
    Route::get('/roles/create', RoleForm::class)->name('roles.create')->middleware('permission:Administrador');
    Route::get('/roles/edit/{roleId}', RoleForm::class)->name('roles.edit')->middleware('permission:Administrador');
    
    // Usuarios y Roles
    Route::get('/usuarios/roles', UserRoleManager::class)->name('users.roles')->middleware('permission:Administrador');

    // Gestión de deudas
    Route::get('/deudas', \App\Http\Livewire\Ventas\DeudasIndex::class)->name('deudas.index')->middleware('permission:Cajero');

    Route::get('/inventario/import-export', function () {
        return view('inventario-import-export');
    })->name('inventario.import-export')->middleware('permission:Cajero');


    

    



});
