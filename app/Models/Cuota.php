<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuota extends Model
{
    protected $table = 'cuotas';

    protected $fillable = [
        'credito_id', 'cliente_id', 'numero_cuota', 'valor_cuota',
        'valor_pagado', 'saldo_cuota', 'fecha_vencimiento', 'fecha_pago',
        'estado', 'dias_mora', 'valor_mora',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'fecha_pago'        => 'date',
            'valor_cuota'       => 'float',
            'valor_pagado'      => 'float',
            'saldo_cuota'       => 'float',
            'valor_mora'        => 'float',
        ];
    }

    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'credito_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'cuota_id');
    }

    public function estaVencida(): bool
    {
        return $this->fecha_vencimiento->isPast() && $this->estado !== 'pagada';
    }

    public function calcularDiasMora(): int
    {
        if ($this->estado === 'pagada') return 0;
        return max(0, $this->fecha_vencimiento->diffInDays(now(), false));
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->whereIn('estado', ['pendiente', 'parcial', 'vencida']);
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencida')
                        ->orWhere(function ($q) {
                            $q->where('estado', 'pendiente')
                            ->where('fecha_vencimiento', '<', today());
                        });
    }

    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha_vencimiento', today());
    }
}