<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    protected $table = 'sectores';

    protected $fillable = [
        'nombre', 'codigo', 'descripcion', 'ciudad', 'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    // ── Relaciones ────────────────────────────────────────────
    public function cobradores(): HasMany
    {
        return $this->hasMany(User::class, 'sector_id');
    }

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class, 'sector_id');
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // ── Helpers ───────────────────────────────────────────────
    public static function generarCodigo(): string
    {
        $ultimo = static::latest()->first();
        $num    = $ultimo ? ((int) substr($ultimo->codigo, 4)) + 1 : 1;
        return 'SEC-' . str_pad($num, 2, '0', STR_PAD_LEFT);
    }
}