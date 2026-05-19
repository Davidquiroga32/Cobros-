<?php

namespace Tests\Unit;

use App\Models\Credito;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditoModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_credito_generates_cuotas(): void
    {
        $cobrador = User::factory()->create(['role' => 'cobrador', 'active' => true]);
        $cliente = Cliente::create([
            'nombre'      => 'Test Cliente',
            'telefono'    => '3100000000',
            'direccion'   => 'Test Direccion',
            'cobrador_id' => $cobrador->id,
            'estado'      => 'activo',
        ]);

        $credito = Credito::create([
            'codigo'           => 'CRD00001',
            'cliente_id'       => $cliente->id,
            'cobrador_id'      => $cobrador->id,
            'creado_por'       => $cobrador->id,
            'monto_prestado'   => 100000,
            'tasa_interes'     => 5,
            'num_cuotas'       => 10,
            'valor_cuota'      => 10500,
            'total_a_pagar'    => 105000,
            'saldo_pendiente'  => 105000,
            'frecuencia'       => 'semanal',
            'fecha_inicio'     => now(),
            'fecha_vencimiento' => now()->addWeeks(10),
            'estado'           => 'activo',
        ]);

        $credito->generarCuotas();

        $this->assertDatabaseCount('cuotas', 10);
        $this->assertEquals(10, $credito->cuotas()->count());
    }

    public function test_credito_porcentaje_pagado(): void
    {
        $cobrador = User::factory()->create(['role' => 'cobrador', 'active' => true]);
        $cliente = Cliente::create([
            'nombre'      => 'Test Cliente',
            'telefono'    => '3100000000',
            'direccion'   => 'Test Direccion',
            'cobrador_id' => $cobrador->id,
            'estado'      => 'activo',
        ]);

        $credito = Credito::create([
            'codigo'           => 'CRD00002',
            'cliente_id'       => $cliente->id,
            'cobrador_id'      => $cobrador->id,
            'creado_por'       => $cobrador->id,
            'monto_prestado'   => 100000,
            'tasa_interes'     => 0,
            'num_cuotas'       => 10,
            'valor_cuota'      => 10000,
            'total_a_pagar'    => 100000,
            'saldo_pendiente'  => 50000,
            'frecuencia'       => 'semanal',
            'fecha_inicio'     => now(),
            'fecha_vencimiento' => now()->addWeeks(10),
            'estado'           => 'activo',
        ]);

        $this->assertEquals(50.0, $credito->porcentajePagado());
    }
}
