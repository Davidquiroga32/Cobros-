<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->string('recibo_numero')->unique();
            $table->foreignId('credito_id')->constrained('creditos');
            $table->foreignId('cuota_id')->nullable()->constrained('cuotas')->nullOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('cobrador_id')->constrained('users');
            $table->decimal('monto_pagado', 12, 2);
            $table->decimal('monto_mora', 10, 2)->default(0);
            $table->decimal('total_recibido', 12, 2);
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'nequi', 'daviplata', 'otro'])->default('efectivo');
            $table->datetime('fecha_pago');
            $table->text('observaciones')->nullable();
            $table->string('comprobante_foto')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->boolean('es_pago_parcial')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};