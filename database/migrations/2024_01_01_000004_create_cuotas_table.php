<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credito_id')->constrained('creditos')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->integer('numero_cuota');
            $table->decimal('valor_cuota', 12, 2);
            $table->decimal('valor_pagado', 12, 2)->default(0);
            $table->decimal('saldo_cuota', 12, 2);
            $table->date('fecha_vencimiento');
            $table->date('fecha_pago')->nullable();
            $table->enum('estado', ['pendiente', 'pagada', 'parcial', 'vencida', 'condonada'])->default('pendiente');
            $table->integer('dias_mora')->default(0);
            $table->decimal('valor_mora', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};