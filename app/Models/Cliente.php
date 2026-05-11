<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre', 'cedula', 'telefono', 'telefono_alt',
        'direccion', 'barrio', 'ciudad', 'referencia_ubicacion',
        'latitud', 'longitud', 'cobrador_id', 'estado', 'notas',
    ];

    protected function casts(): array
    {
        return [
            'latitud' => 'float',
            'longitud' => 'float',
        ];
    }

    // Relaciones
    public function cobrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cobrador_id');
    }

    public function creditos(): HasMany
    {
        return $this->hasMany(Credito::class, 'cliente_id');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class, 'cliente_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'cliente_id');
    }

    // Helpers
    public function saldoPendiente(): float
    {
        return $this->creditos()
            ->where('estado', '!=', 'pagado')
            ->sum('saldo_pendiente');
    }

    public function enMora(): bool
    {
        return $this->creditos()
            ->where('estado', 'mora')
            ->exists();
    }

    public function creditoActivo(): ?Credito
    {
        return $this->creditos()
            ->whereIn('estado', ['activo', 'mora', 'al_dia'])
            ->latest()
            ->first();
    }

    public function cuotasPendientesHoy()
    {
        return $this->cuotas()
            ->whereDate('fecha_vencimiento', today())
            ->where('estado', 'pendiente');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeEnMora($query)
    {
        return $query->whereHas('creditos', fn ($q) => $q->where('estado', 'mora'));
    }

    public function scopeDelCobrador($query, int $cobradorId)
    {
        return $query->where('cobrador_id', $cobradorId);
    }
}