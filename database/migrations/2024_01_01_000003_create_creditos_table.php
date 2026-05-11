<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creditos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('cobrador_id')->constrained('users');
            $table->foreignId('creado_por')->constrained('users');
            $table->decimal('monto_prestado', 12, 2);
            $table->decimal('tasa_interes', 5, 2)->default(0);
            $table->integer('num_cuotas');
            $table->decimal('valor_cuota', 12, 2);
            $table->decimal('total_a_pagar', 12, 2);
            $table->decimal('saldo_pendiente', 12, 2);
            $table->enum('frecuencia', ['diaria', 'semanal', 'quincenal', 'mensual'])->default('semanal');
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento');
            $table->date('proxima_fecha_pago')->nullable();
            $table->enum('estado', ['activo', 'al_dia', 'mora', 'pagado', 'cancelado'])->default('activo');
            $table->integer('dias_mora')->default(0);
            $table->decimal('valor_mora', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creditos');
    }
};