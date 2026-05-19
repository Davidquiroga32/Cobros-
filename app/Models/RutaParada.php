<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RutaParada extends Model
{
    protected $table = 'ruta_paradas';

    protected $fillable = [
        'ruta_id', 'cliente_id', 'cuota_id', 'orden',
        'estado', 'monto_esperado', 'monto_cobrado',
        'hora_visita', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'hora_visita'    => 'datetime',
            'monto_esperado' => 'float',
            'monto_cobrado'  => 'float',
        ];
    }

    // ── Relaciones ────────────────────────────────────────────
    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cuota(): BelongsTo
    {
        return $this->belongsTo(Cuota::class, 'cuota_id');
    }

    // ── Helpers ───────────────────────────────────────────────
    public function marcarVisitado(float $montoCobrado = 0, ?string $obs = null): void
    {
        $this->update([
            'estado'        => 'visitado',
            'monto_cobrado' => $montoCobrado,
            'hora_visita'   => now(),
            'observaciones' => $obs,
        ]);

        $this->ruta->recalcularProgreso();
    }
}