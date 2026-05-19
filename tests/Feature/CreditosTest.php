<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditosTest extends TestCase
{
    use RefreshDatabase;

    private User $cobrador;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cobrador = User::factory()->create([
            'role'   => 'cobrador',
            'active' => true,
        ]);

        $this->cliente = Cliente::create([
            'nombre'      => 'Cliente Test',
            'telefono'    => '3101234567',
            'direccion'   => 'Calle Test #123',
            'cobrador_id' => $this->cobrador->id,
            'estado'      => 'activo',
        ]);
    }

    public function test_cobrador_can_create_credit(): void
    {
        $response = $this->actingAs($this->cobrador)->post('/cobrador/creditos', [
            'cliente_id'     => $this->cliente->id,
            'monto_prestado' => 500000,
            'tasa_interes'   => 5,
            'num_cuotas'     => 12,
            'frecuencia'     => 'semanal',
            'fecha_inicio'   => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect('/cobrador/creditos');

        $this->assertDatabaseHas('creditos', [
            'cliente_id'     => $this->cliente->id,
            'cobrador_id'    => $this->cobrador->id,
            'monto_prestado' => 500000,
            'num_cuotas'     => 12,
        ]);

        $this->assertDatabaseCount('cuotas', 12);
    }

    public function test_cobrador_can_view_own_creditos(): void
    {
        Credito::create([
            'codigo'           => Credito::generarCodigo(),
            'cliente_id'       => $this->cliente->id,
            'cobrador_id'      => $this->cobrador->id,
            'creado_por'       => $this->cobrador->id,
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

        $response = $this->actingAs($this->cobrador)
            ->get('/cobrador/creditos');

        $response->assertStatus(200);
        $response->assertSee('Cliente Test');
    }

    public function test_cobrador_cannot_access_other_cobrador_credito(): void
    {
        $otroCobrador = User::factory()->create(['role' => 'cobrador', 'active' => true]);
        $otroCliente = Cliente::create([
            'nombre'      => 'Otro Cliente',
            'telefono'    => '3200000000',
            'direccion'   => 'Otra Calle',
            'cobrador_id' => $otroCobrador->id,
            'estado'      => 'activo',
        ]);

        $credito = Credito::create([
            'codigo'           => Credito::generarCodigo(),
            'cliente_id'       => $otroCliente->id,
            'cobrador_id'      => $otroCobrador->id,
            'creado_por'       => $otroCobrador->id,
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

        $response = $this->actingAs($this->cobrador)
            ->get("/cobrador/creditos/{$credito->id}");

        $response->assertStatus(403);
    }
}
