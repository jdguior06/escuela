<?php

use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\ControlCertificacionController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FranjaHorariaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PagoFacilWebhookController;
use App\Http\Controllers\PlanPagoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TipoCursoController;
use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:Propietario|Secretaria'])->group(function () {
    Route::resource('usuarios', UsuarioController::class)->except(['show']);
    Route::resource('vehiculos', VehiculoController::class)->except(['show']);
    Route::resource('cursos', CursoController::class)->except(['show']);
    Route::get('/pagos', [PagoController::class, 'listado'])->name('pagos.listado');
});

Route::middleware(['auth', 'role:Propietario'])->group(function () {
    // Métodos de pago son fijos (Efectivo/QR): solo listado, sin crear/editar/eliminar.
    Route::get('/metodos-pago', [MetodoPagoController::class, 'index'])->name('metodos-pago.index');
    // Tipos de vehículo son fijos (Auto/Moto/Camión): solo listado, sin crear/editar/eliminar.
    Route::get('/tipos-vehiculo', [TipoVehiculoController::class, 'index'])->name('tipos-vehiculo.index');
    Route::resource('franjas-horarias', FranjaHorariaController::class)->except(['show'])->parameters(['franjas-horarias' => 'franjaHoraria']);
    Route::resource('planes-pago', PlanPagoController::class)->except(['show'])->parameters(['planes-pago' => 'planPago']);
    Route::resource('tipos-curso', TipoCursoController::class)->except(['show'])->parameters(['tipos-curso' => 'tipoCurso']);
    Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
    Route::patch('/roles/{rol}/menus', [RolController::class, 'actualizarMenus'])->name('roles.actualizarMenus');
    Route::get('/bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
});

Route::get('/buscar', [BusquedaController::class, 'index'])->middleware('auth')->name('buscar');

Route::middleware(['auth', 'role:Propietario|Secretaria|Estudiante'])->group(function () {
    Route::resource('reservas', ReservaController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('inscripciones', InscripcionController::class)->only(['index', 'create', 'store']);

    Route::get('/inscripciones/{inscripcion}/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::get('/pagos/{pago}/estado', [PagoController::class, 'estado'])->name('pagos.estado');
    Route::get('/pagos/{pago}/recibo', [PagoController::class, 'recibo'])->name('pagos.recibo');
});

Route::middleware(['auth', 'role:Propietario|Instructor'])->group(function () {
    Route::get('/control-certificacion/{inscripcion}/nueva', [ControlCertificacionController::class, 'create'])->name('control-certificacion.create');
    Route::post('/control-certificacion', [ControlCertificacionController::class, 'store'])->name('control-certificacion.store');
});

Route::middleware(['auth', 'role:Propietario|Instructor|Estudiante'])->group(function () {
    Route::get('/control-certificacion', [ControlCertificacionController::class, 'index'])->name('control-certificacion.index');
    Route::get('/control-certificacion/{controlCertificacion}/pdf', [ControlCertificacionController::class, 'descargar'])->name('control-certificacion.pdf');
});

Route::prefix('pagofacil')->name('pagofacil.')->group(function () {
    Route::post('callback', [PagoFacilWebhookController::class, 'callback'])->name('callback');
    Route::get('retorno', [PagoFacilWebhookController::class, 'retorno'])->name('retorno');
    Route::post('factura', [PagoFacilWebhookController::class, 'factura'])->name('factura');
});

require __DIR__.'/auth.php';
