<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cabecera de ruta diaria por cobrador
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cobrador_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('caja_id')->nullable()->constrained('cajas')->nullOnDelete();
            $table->date('fecha');

            $table->integer('total_paradas')->default(0);
            $table->integer('paradas_completadas')->default(0);
            $table->integer('progreso')->storedAs('IF(total_paradas > 0, ROUND((paradas_completadas * 100) / total_paradas), 0)')
                  ->comment('Porcentaje calculado automáticamente');

            $table->enum('estado', ['pendiente', 'en_curso', 'completada'])->default('pendiente');
            $table->timestamps();

            $table->unique(['cobrador_id', 'fecha']);
            $table->index(['cobrador_id', 'fecha']);
        });

        // Paradas individuales dentro de una ruta
        Schema::create('ruta_paradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained('rutas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('cuota_id')->nullable()->constrained('cuotas')->nullOnDelete();

            $table->integer('orden')->default(0)->comment('Orden de visita en la ruta');
            $table->enum('estado', ['pendiente', 'visitado', 'no_encontrado', 'reagendado'])->default('pendiente');

            $table->decimal('monto_esperado', 12, 2)->default(0);
            $table->decimal('monto_cobrado', 12, 2)->default(0);

            $table->datetime('hora_visita')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index(['ruta_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruta_paradas');
        Schema::dropIfExists('rutas');
    }
};