<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Usuarios ──────────────────────────────────────────────────────────
        $admin = User::factory()->create([
            'name'     => 'Carlos Administrador',
            'email'    => 'admin@smartpay.co',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'phone'    => '3001234567',
        ]);

        $cobrador = User::factory()->create([
            'name'     => 'Ana Martínez',
            'email'    => 'cobrador@smartpay.co',
            'password' => Hash::make('password'),
            'role'     => 'cobrador',
            'phone'    => '3009876543',
        ]);

        // ── Datos de clientes ─────────────────────────────────────────────────
        $clientesData = [
            ['nombre' => 'Inversiones López',  'cedula' => '10123456', 'telefono' => '3101234567', 'direccion' => 'Cra 5 #23-10, Barrio Centro', 'barrio' => 'Centro'],
            ['nombre' => 'Marcos Pedraza',     'cedula' => '10234567', 'telefono' => '3112345678', 'direccion' => 'Cll 8 #15-20, Barrio La 40', 'barrio' => 'La 40'],
            ['nombre' => 'Tienda La 40',       'cedula' => '10345678', 'telefono' => '3123456789', 'direccion' => 'Av. 40 #10-5',               'barrio' => 'La 40'],
            ['nombre' => 'Julia Méndez',       'cedula' => '10456789', 'telefono' => '3134567890', 'direccion' => 'Cra 3 #45-12, Barzal',       'barrio' => 'Barzal'],
            ['nombre' => 'Ferretería El Gato', 'cedula' => '10567890', 'telefono' => '3145678901', 'direccion' => 'Cll 30 #7-8',                'barrio' => 'Macarena'],
            ['nombre' => 'Rosa Morales',       'cedula' => '10678901', 'telefono' => '3156789012', 'direccion' => 'Cra 9 #12-5, Popular',       'barrio' => 'Popular'],
        ];

        // ── Contador de código incremental (sin depender del modelo) ──────────
        $codigoContador = 1;

        foreach ($clientesData as $data) {
            $cliente = Cliente::create(array_merge($data, [
                'cobrador_id' => $cobrador->id,
                'ciudad'      => 'Villavicencio',
                'estado'      => 'activo',
            ]));

            // Parámetros del crédito
            $montoPrestado = rand(3, 20) * 100000;          // 300k – 2M en múltiplos de 100k
            $numCuotas     = rand(10, 24);
            $tasaInteres   = 5;
            $totalPagar    = $montoPrestado * (1 + $tasaInteres / 100);
            $valorCuota    = round($totalPagar / $numCuotas / 1000) * 1000; // redondear a miles
            $fechaInicio   = Carbon::now()->subDays(rand(5, 30))->startOfDay();

            // Código único generado aquí, sin llamar al modelo (evita la condición de carrera)
            $codigo = 'CRD' . str_pad($codigoContador++, 5, '0', STR_PAD_LEFT);

            $credito = Credito::create([
                'codigo'              => $codigo,
                'cliente_id'          => $cliente->id,
                'cobrador_id'         => $cobrador->id,
                'creado_por'          => $admin->id,
                'monto_prestado'      => $montoPrestado,
                'tasa_interes'        => $tasaInteres,
                'num_cuotas'          => $numCuotas,
                'valor_cuota'         => $valorCuota,
                'total_a_pagar'       => $totalPagar,
                'saldo_pendiente'     => $totalPagar,
                'frecuencia'          => 'semanal',
                'fecha_inicio'        => $fechaInicio,
                'fecha_vencimiento'   => $fechaInicio->copy()->addWeeks($numCuotas),
                'proxima_fecha_pago'  => Carbon::today(),
                'estado'              => 'activo',
            ]);

            $credito->generarCuotas();

            // Pagar algunas cuotas pasadas (0 a 3)
            $cuotasPagadas = rand(0, 3);

            if ($cuotasPagadas > 0) {
                $cuotas = $credito->cuotas()
                    ->orderBy('numero_cuota')
                    ->take($cuotasPagadas)
                    ->get();

                $montoAcumuladoPagado = 0;

                foreach ($cuotas as $cuota) {
                    $reciboNumero = 'REC' . str_pad(Pago::count() + 1, 6, '0', STR_PAD_LEFT);

                    Pago::create([
                        'recibo_numero'   => $reciboNumero,
                        'credito_id'      => $credito->id,
                        'cuota_id'        => $cuota->id,
                        'cliente_id'      => $cliente->id,
                        'cobrador_id'     => $cobrador->id,
                        'monto_pagado'    => $cuota->valor_cuota,
                        'monto_mora'      => 0,
                        'total_recibido'  => $cuota->valor_cuota,
                        'metodo_pago'     => 'efectivo',
                        'fecha_pago'      => $cuota->fecha_vencimiento->copy()->addHours(rand(8, 17)),
                        'es_pago_parcial' => false,
                    ]);

                    $cuota->update([
                        'estado'       => 'pagada',
                        'valor_pagado' => $cuota->valor_cuota,
                        'saldo_cuota'  => 0,
                        'fecha_pago'   => $cuota->fecha_vencimiento,
                    ]);

                    $montoAcumuladoPagado += $cuota->valor_cuota;
                }

                // Actualizar saldo del crédito de una sola vez
                $credito->decrement('saldo_pendiente', $montoAcumuladoPagado);
            }
        }
    }
}