<?php

use App\Http\Controllers\Admin\CajaController as AdminCajaController;
use App\Http\Controllers\Admin\ClienteController as AdminClienteController;
use App\Http\Controllers\Admin\CreditoController as AdminCreditoController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\CobradorEstadoController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\CobradorEstadoApiController;
use App\Http\Controllers\Cobrador\AgendaController;
use App\Http\Controllers\Cobrador\CajaController as CobradorCajaController;
use App\Http\Controllers\Cobrador\ClienteController;
use App\Http\Controllers\Cobrador\CreditoController as CobradorCreditoController;
use App\Http\Controllers\Cobrador\DashboardController;
use App\Http\Controllers\Cobrador\PagoController;
use App\Http\Controllers\Cobrador\RutaController;
use Illuminate\Support\Facades\Route;

// ─── RAÍZ ─────────────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ─── DASHBOARD (redirect por rol) ─────────────────────────────────────────────
Route::get('/dashboard', function () {
    return match (auth()->user()->role) {
        'admin'    => redirect()->route('admin.dashboard'),
        'cobrador' => redirect()->route('cobrador.dashboard'),
        default    => redirect()->route('login'),
    };
})->middleware('auth')->name('dashboard');

// ─── API ───────────────────────────────────────────────────────────────────────
// CORRECCIÓN DE SEGURIDAD: La ruta de sync debe estar autenticada.
// Usar sanctum si la app móvil envía tokens, o session auth si es web.
Route::post('/cobradores/sync', [CobradorEstadoApiController::class, 'sync'])
    ->middleware('auth'); // ← CRÍTICO: estaba sin middleware

// ─── COBRADOR ─────────────────────────────────────────────────────────────────
Route::prefix('cobrador')
    ->name('cobrador.')
    ->middleware(['auth', 'role:cobrador,admin'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Clientes
        Route::get('/clientes',                  [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/crear',            [ClienteController::class, 'create'])->name('clientes.create');
        Route::post('/clientes',                 [ClienteController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/{cliente}',        [ClienteController::class, 'show'])->name('clientes.show');
        Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
        Route::put('/clientes/{cliente}',        [ClienteController::class, 'update'])->name('clientes.update');

        // Creditos (cobrador puede crear creditos para sus clientes)
        Route::get('/creditos',              [CobradorCreditoController::class, 'index'])->name('creditos.index');
        Route::get('/creditos/crear',        [CobradorCreditoController::class, 'create'])->name('creditos.create');
        Route::post('/creditos',             [CobradorCreditoController::class, 'store'])->name('creditos.store');
        Route::get('/creditos/{credito}',    [CobradorCreditoController::class, 'show'])->name('creditos.show');

        // Agenda
        Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda');

        // Pagos
        Route::get('/pagos',                 [PagoController::class, 'index'])->name('pagos.index');
        Route::get('/pagos/cuota/{cuota}',   [PagoController::class, 'create'])->name('pagos.create');
        Route::post('/pagos/cuota/{cuota}',  [PagoController::class, 'store'])->name('pagos.store');

        // ── Ruta del día ─────────────────────────────────────────────────────
        Route::get('/ruta',                              [RutaController::class, 'index'])->name('ruta.index');
        Route::patch('/ruta/paradas/{parada}',           [RutaController::class, 'actualizarParada'])->name('ruta.parada.update');
        Route::post('/ruta/{ruta}/reordenar',            [RutaController::class, 'reordenar'])->name('ruta.reordenar');

        // ── Caja ─────────────────────────────────────────────────────────────
        Route::get('/caja',          [CobradorCajaController::class, 'index'])->name('caja.index');
        Route::post('/caja/abrir',   [CobradorCajaController::class, 'abrir'])->name('caja.abrir');
        Route::post('/caja/cerrar',  [CobradorCajaController::class, 'cerrar'])->name('caja.cerrar');
    });

// ─── ADMIN ────────────────────────────────────────────────────────────────────
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // ── Clientes ─────────────────────────────────────────────────────────
        Route::get('/clientes',                  [AdminClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/crear',            [AdminClienteController::class, 'create'])->name('clientes.create');
        Route::post('/clientes',                 [AdminClienteController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/{cliente}',        [AdminClienteController::class, 'show'])->name('clientes.show');
        Route::get('/clientes/{cliente}/editar', [AdminClienteController::class, 'edit'])->name('clientes.edit');
        Route::put('/clientes/{cliente}',        [AdminClienteController::class, 'update'])->name('clientes.update');
        Route::delete('/clientes/{cliente}',     [AdminClienteController::class, 'destroy'])->name('clientes.destroy');

        // ── Créditos ─────────────────────────────────────────────────────────
        Route::get('/creditos',                  [AdminCreditoController::class, 'index'])->name('creditos.index');
        Route::get('/creditos/crear',            [AdminCreditoController::class, 'create'])->name('creditos.create');
        Route::post('/creditos',                 [AdminCreditoController::class, 'store'])->name('creditos.store');
        Route::get('/creditos/{credito}',        [AdminCreditoController::class, 'show'])->name('creditos.show');
        Route::get('/creditos/{credito}/editar', [AdminCreditoController::class, 'edit'])->name('creditos.edit');
        Route::put('/creditos/{credito}',        [AdminCreditoController::class, 'update'])->name('creditos.update');
        Route::delete('/creditos/{credito}',     [AdminCreditoController::class, 'destroy'])->name('creditos.destroy');

        // AJAX: clientes por cobrador
        Route::get('/api/cobrador/{cobrador}/clientes', [AdminCreditoController::class, 'clientesPorCobrador'])
            ->name('api.clientes-por-cobrador');

        // ── Usuarios / Cobradores ─────────────────────────────────────────────
        Route::get('/usuarios',                    [AdminUserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear',              [AdminUserController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios',                   [AdminUserController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{usuario}/editar',   [AdminUserController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{usuario}',          [AdminUserController::class, 'update'])->name('usuarios.update');
        Route::patch('/usuarios/{usuario}/toggle', [AdminUserController::class, 'toggleActive'])->name('usuarios.toggle');

        // ── Estado operativo (vista real, no JSON) ───────────────────────────
        Route::get('/cobradores/estado', [CobradorEstadoController::class, 'index'])->name('cobradores.estado');

        // ── Sectores ─────────────────────────────────────────────────────────
        Route::get('/sectores',                     [SectorController::class, 'index'])->name('sectores.index');
        Route::get('/sectores/crear',               [SectorController::class, 'create'])->name('sectores.create');
        Route::post('/sectores',                    [SectorController::class, 'store'])->name('sectores.store');
        Route::get('/sectores/{sector}/editar',     [SectorController::class, 'edit'])->name('sectores.edit');
        Route::put('/sectores/{sector}',            [SectorController::class, 'update'])->name('sectores.update');
        Route::delete('/sectores/{sector}',         [SectorController::class, 'destroy'])->name('sectores.destroy');
        Route::patch('/sectores/{sector}/toggle',   [SectorController::class, 'toggleActivo'])->name('sectores.toggle');

        // ── Cajas ─────────────────────────────────────────────────────────────
        Route::get('/cajas',                    [AdminCajaController::class, 'index'])->name('cajas.index');
        Route::get('/cajas/crear',              [AdminCajaController::class, 'create'])->name('cajas.create');
        Route::post('/cajas',                   [AdminCajaController::class, 'store'])->name('cajas.store');
        Route::get('/cajas/{caja}',             [AdminCajaController::class, 'show'])->name('cajas.show');
        Route::post('/cajas/{caja}/cerrar',     [AdminCajaController::class, 'cerrar'])->name('cajas.cerrar');
    });

require __DIR__ . '/auth.php';