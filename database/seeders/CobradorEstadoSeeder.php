<?php

namespace Database\Seeders;

use App\Models\CobradorEstado;
use App\Models\User;
use Illuminate\Database\Seeder;

class CobradorEstadoSeeder extends Seeder
{
    public function run(): void
    {
        $cobradores = User::where('role', 'cobrador')->get();

        foreach ($cobradores as $cobrador) {

            CobradorEstado::create([
                'cobrador_id' => $cobrador->id,
                'cn' => 'CN-' . rand(100, 999),
                'ubicacion_actual' => 'Villavicencio',
                'estado' => 'en_ruta',
                'score' => rand(70, 100),
                'caja_actual' => 'CJ-' . rand(1000, 9999),
                'fecha_caja' => now(),
                'caja_inicial' => 100000,
                'caja_final' => rand(50000, 150000),
                'progreso_ruta' => rand(10, 100),
                'ultima_sincronizacion' => now(),
                'pin_dispositivo' => rand(1000, 9999),
                'version_app' => '1.0.0',
                'conectado' => rand(0, 1),
            ]);
        }
    }
}