<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CajaController as ApiCajaController;
use App\Http\Controllers\Api\ClienteController as ApiClienteController;
use App\Http\Controllers\Api\CobradorEstadoApiController;
use App\Http\Controllers\Api\CreditoController as ApiCreditoController;
use App\Http\Controllers\Api\CuotaController as ApiCuotaController;
use App\Http\Controllers\Api\PagoController as ApiPagoController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/clientes', [ApiClienteController::class, 'index']);
    Route::get('/clientes/{cliente}', [ApiClienteController::class, 'show']);
    Route::post('/clientes', [ApiClienteController::class, 'store']);

    Route::get('/creditos', [ApiCreditoController::class, 'index']);
    Route::get('/creditos/{credito}', [ApiCreditoController::class, 'show']);

    Route::get('/cuotas/hoy', [ApiCuotaController::class, 'hoy']);
    Route::get('/cuotas/atrasadas', [ApiCuotaController::class, 'atrasadas']);

    Route::post('/pagos', [ApiPagoController::class, 'store']);
    Route::get('/pagos/hoy', [ApiPagoController::class, 'hoy']);

    Route::get('/caja/actual', [ApiCajaController::class, 'actual']);
    Route::post('/caja/abrir', [ApiCajaController::class, 'abrir']);
    Route::post('/caja/cerrar', [ApiCajaController::class, 'cerrar']);

    Route::post('/estado/sync', [CobradorEstadoApiController::class, 'sync']);
});
