<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cobradores_estado', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cobrador_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('cn')->nullable();

            $table->string('ubicacion_actual')->nullable();

            $table->enum('estado', [
                'disponible',
                'en_ruta',
                'pausado',
                'sincronizando',
                'offline'
            ])->default('offline');

            $table->integer('score')->default(0);

            $table->string('caja_actual')->nullable();

            $table->date('fecha_caja')->nullable();

            $table->decimal('caja_inicial', 12, 2)->default(0);

            $table->decimal('caja_final', 12, 2)->default(0);

            $table->integer('progreso_ruta')->default(0);

            $table->timestamp('ultima_sincronizacion')->nullable();

            $table->string('pin_dispositivo')->nullable();

            $table->string('version_app')->nullable();

            $table->boolean('conectado')->default(false);

            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobradores_estado');
    }
};
