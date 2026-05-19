<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'active', 'cn', 'sector_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'active'            => 'boolean',
        ];
    }

    // ── Roles ─────────────────────────────────────────────────
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isCobrador(): bool { return $this->role === 'cobrador'; }

    // ── Relaciones ────────────────────────────────────────────
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'cobrador_id');
    }

    public function creditos(): HasMany
    {
        return $this->hasMany(Credito::class, 'cobrador_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'cobrador_id');
    }

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class, 'cobrador_id');
    }

    public function rutas(): HasMany
    {
        return $this->hasMany(Ruta::class, 'cobrador_id');
    }

    public function estadoOperativo(): HasOne
    {
        return $this->hasOne(CobradorEstado::class, 'cobrador_id');
    }

    // ── Estadísticas ──────────────────────────────────────────
    public function totalCobradoHoy(): float
    {
        return $this->pagos()
            ->whereDate('fecha_pago', today())
            ->sum('monto_pagado');
    }

    public function clientesPendientesHoy(): int
    {
        return $this->clientes()
            ->whereHas('cuotas', fn ($q) =>
                $q->whereDate('fecha_vencimiento', today())
                  ->where('estado', 'pendiente')
            )
            ->count();
    }

    /**
     * Caja activa del día (abierta).
     */
    public function cajaHoy(): ?Caja
    {
        return $this->cajas()
            ->whereDate('fecha_jornada', today())
            ->where('estado', 'abierta')
            ->first();
    }

    /**
     * Ruta activa del día.
     */
    public function rutaHoy(): ?Ruta
    {
        return $this->rutas()
            ->whereDate('fecha', today())
            ->first();
    }
}