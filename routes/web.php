<?php

use App\Http\Controllers\Cobrador\AgendaController;
use App\Http\Controllers\Cobrador\ClienteController;
use App\Http\Controllers\Cobrador\DashboardController;
use App\Http\Controllers\Cobrador\PagoController;
//use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Auth routes (Breeze / manual)
//Auth::routes(['register' => false]);

// ─── COBRADOR ────────────────────────────────────────────────────────────────
Route::prefix('cobrador')
    ->name('cobrador.')
    ->middleware(['auth', 'role:cobrador,admin'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Clientes
        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');

        // Agenda de cobro
        Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda');

        // Pagos
        Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
        Route::get('/pagos/cuota/{cuota}', [PagoController::class, 'create'])->name('pagos.create');
        Route::post('/pagos/cuota/{cuota}', [PagoController::class, 'store'])->name('pagos.store');
    });

// ─── ADMIN ───────────────────────────────────────────────────────────────────
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        // TODO: Panel de administrador (siguiente fase)
        Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');
    });

// Redirect after login based on role
Route::get('/dashboard', function () {
    return match (auth()->user()->role) {
        'admin'    => redirect()->route('admin.dashboard'),
        'cobrador' => redirect()->route('cobrador.dashboard'),
        default    => redirect()->route('login'),
    };
})->middleware('auth')->name('dashboard');

require __DIR__.'/auth.php';