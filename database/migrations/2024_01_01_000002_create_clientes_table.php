<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cedula')->unique()->nullable();
            $table->string('telefono');
            $table->string('telefono_alt')->nullable();
            $table->text('direccion');
            $table->string('barrio')->nullable();
            $table->string('ciudad')->nullable();
            $table->text('referencia_ubicacion')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->foreignId('cobrador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estado', ['activo', 'inactivo', 'bloqueado'])->default('activo');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};