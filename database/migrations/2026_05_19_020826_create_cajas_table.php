<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->comment('Ej: CAJA-20260518-001');

            $table->foreignId('cobrador_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sector_id')->nullable()->constrained('sectores')->nullOnDelete();
            $table->foreignId('abierta_por')->constrained('users');
            $table->foreignId('cerrada_por')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('monto_inicial', 12, 2)->default(0)
                  ->comment('Efectivo con el que sale el cobrador');
            $table->decimal('monto_cobrado', 12, 2)->default(0)
                  ->comment('Suma de pagos registrados durante la jornada');
            $table->decimal('monto_gastos', 12, 2)->default(0)
                  ->comment('Gastos operativos (combustible, etc.)');
            $table->decimal('monto_final', 12, 2)->default(0)
                  ->comment('Calculado: inicial + cobrado - gastos');

            $table->enum('estado', ['abierta', 'cerrada', 'cuadrada'])->default('abierta');

            $table->datetime('fecha_apertura');
            $table->datetime('fecha_cierre')->nullable();
            $table->date('fecha_jornada');

            $table->text('notas_apertura')->nullable();
            $table->text('notas_cierre')->nullable();

            $table->timestamps();

            $table->index(['cobrador_id', 'fecha_jornada']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};