<?php

use App\Http\Controllers\Admin\ClienteController as AdminClienteController;
use App\Http\Controllers\Admin\CreditoController as AdminCreditoController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CobradorEstadoController;
use App\Http\Controllers\Cobrador\AgendaController;
use App\Http\Controllers\Cobrador\ClienteController;
use App\Http\Controllers\Cobrador\DashboardController;
use App\Http\Controllers\Cobrador\PagoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CobradorEstadoApiController;


Route::post(
    '/cobradores/sync',
    [CobradorEstadoApiController::class, 'sync']
);

Route::get('/', fn () => redirect()->route('login'));

// ─── COBRADOR ─────────────────────────────────────────────────────────────────
Route::prefix('cobrador')
    ->name('cobrador.')
    ->middleware(['auth', 'role:cobrador,admin'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Clientes
        Route::get('/clientes',                [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/crear',          [ClienteController::class, 'create'])->name('clientes.create');
        Route::post('/clientes',               [ClienteController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/{cliente}',      [ClienteController::class, 'show'])->name('clientes.show');

        // Agenda de cobro
        Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda');

        // Pagos
        Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
        Route::get('/pagos/cuota/{cuota}', [PagoController::class, 'create'])->name('pagos.create');
        Route::post('/pagos/cuota/{cuota}', [PagoController::class, 'store'])->name('pagos.store');
    });

// ─── ADMIN ────────────────────────────────────────────────────────────────────
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // ── Clientes (CRUD completo) ──────────────────────────────────────────
        Route::get('/cobradores/estado',         [CobradorEstadoController::class, 'index'])->name('cobradores.estado');
        Route::get('/clientes',                  [AdminClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/crear',            [AdminClienteController::class, 'create'])->name('clientes.create');
        Route::post('/clientes',                 [AdminClienteController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/{cliente}',        [AdminClienteController::class, 'show'])->name('clientes.show');
        Route::get('/clientes/{cliente}/editar', [AdminClienteController::class, 'edit'])->name('clientes.edit');
        Route::put('/clientes/{cliente}',        [AdminClienteController::class, 'update'])->name('clientes.update');
        Route::delete('/clientes/{cliente}',     [AdminClienteController::class, 'destroy'])->name('clientes.destroy');

        // ── Créditos ──────────────────────────────────────────────────────────
        Route::get('/creditos',                [AdminCreditoController::class, 'index'])->name('creditos.index');
        Route::get('/creditos/crear',          [AdminCreditoController::class, 'create'])->name('creditos.create');
        Route::post('/creditos',               [AdminCreditoController::class, 'store'])->name('creditos.store');
        Route::get('/creditos/{credito}',      [AdminCreditoController::class, 'show'])->name('creditos.show');
        Route::delete('/creditos/{credito}',   [AdminCreditoController::class, 'destroy'])->name('creditos.destroy');

        // ── API: clientes por cobrador (AJAX) ─────────────────────────────────
        Route::get('/api/cobrador/{cobrador}/clientes', [AdminCreditoController::class, 'clientesPorCobrador'])
            ->name('api.clientes-por-cobrador');

        // ── Usuarios / Cobradores ─────────────────────────────────────────────
        Route::get('/usuarios',                    [AdminUserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear',              [AdminUserController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios',                   [AdminUserController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{usuario}/editar',   [AdminUserController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{usuario}',          [AdminUserController::class, 'update'])->name('usuarios.update');
        Route::patch('/usuarios/{usuario}/toggle', [AdminUserController::class, 'toggleActive'])->name('usuarios.toggle');
    });

// ─── DASHBOARD (redirect por rol) ─────────────────────────────────────────────
Route::get('/dashboard', function () {
    return match (auth()->user()->role) {
        'admin'    => redirect()->route('admin.dashboard'),
        'cobrador' => redirect()->route('cobrador.dashboard'),
        default    => redirect()->route('login'),
    };
})->middleware('auth')->name('dashboard');

require __DIR__.'/auth.php';