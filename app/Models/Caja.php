<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = [
        'codigo', 'cobrador_id', 'sector_id', 'abierta_por', 'cerrada_por',
        'monto_inicial', 'monto_cobrado', 'monto_gastos', 'monto_final',
        'estado', 'fecha_apertura', 'fecha_cierre', 'fecha_jornada',
        'notas_apertura', 'notas_cierre',
    ];

    protected function casts(): array
    {
        return [
            'fecha_apertura'  => 'datetime',
            'fecha_cierre'    => 'datetime',
            'fecha_jornada'   => 'date',
            'monto_inicial'   => 'float',
            'monto_cobrado'   => 'float',
            'monto_gastos'    => 'float',
            'monto_final'     => 'float',
        ];
    }

    // ── Relaciones ────────────────────────────────────────────
    public function cobrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cobrador_id');
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function abiertaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'abierta_por');
    }

    public function cerradaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrada_por');
    }

    public function rutas(): HasMany
    {
        return $this->hasMany(Ruta::class, 'caja_id');
    }

    // ── Helpers ───────────────────────────────────────────────
    public function estaAbierta(): bool
    {
        return $this->estado === 'abierta';
    }

    /**
     * Recalcula monto_final y monto_cobrado desde pagos reales.
     * Llamar tras registrar un pago para mantener consistencia.
     */
    public function sincronizarMontos(): void
    {
        $cobrado = \App\Models\Pago::where('cobrador_id', $this->cobrador_id)
            ->whereDate('fecha_pago', $this->fecha_jornada)
            ->sum('monto_pagado');

        $this->update([
            'monto_cobrado' => $cobrado,
            'monto_final'   => $this->monto_inicial + $cobrado - $this->monto_gastos,
        ]);
    }

    /**
     * Cierra la caja calculando el monto final.
     */
    public function cerrar(int $cerradaPorId, float $gastos = 0, ?string $notas = null): void
    {
        $this->sincronizarMontos();

        $this->update([
            'estado'       => 'cerrada',
            'cerrada_por'  => $cerradaPorId,
            'monto_gastos' => $gastos,
            'monto_final'  => $this->monto_inicial + $this->monto_cobrado - $gastos,
            'fecha_cierre' => now(),
            'notas_cierre' => $notas,
        ]);
    }

    public static function generarCodigo(): string
    {
        $hoy    = now()->format('Ymd');
        $ultimo = static::whereDate('fecha_jornada', today())->count() + 1;
        return "CAJA-{$hoy}-" . str_pad($ultimo, 3, '0', STR_PAD_LEFT);
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeAbiertas($query)
    {
        return $query->where('estado', 'abierta');
    }

    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha_jornada', today());
    }
}