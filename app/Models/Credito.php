<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Credito extends Model
{
    use SoftDeletes;

    protected $table = 'creditos';

    protected $fillable = [
        'codigo', 'cliente_id', 'cobrador_id', 'creado_por',
        'monto_prestado', 'tasa_interes', 'num_cuotas', 'valor_cuota',
        'total_a_pagar', 'saldo_pendiente', 'frecuencia',
        'fecha_inicio', 'fecha_vencimiento', 'proxima_fecha_pago',
        'estado', 'dias_mora', 'valor_mora', 'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio'       => 'date',
            'fecha_vencimiento'  => 'date',
            'proxima_fecha_pago' => 'date',
            'monto_prestado'     => 'float',
            'tasa_interes'       => 'float',
            'valor_cuota'        => 'float',
            'total_a_pagar'      => 'float',
            'saldo_pendiente'    => 'float',
            'valor_mora'         => 'float',
        ];
    }

    // Relaciones
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cobrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cobrador_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class, 'credito_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'credito_id');
    }

    // Helpers
    public function cuotasPagadas(): int
    {
        return $this->cuotas()->where('estado', 'pagada')->count();
    }

    public function cuotasPendientes(): int
    {
        return $this->cuotas()->whereIn('estado', ['pendiente', 'vencida', 'parcial'])->count();
    }

    public function porcentajePagado(): float
    {
        if ($this->total_a_pagar == 0) return 0;
        $pagado = $this->total_a_pagar - $this->saldo_pendiente;
        return round(($pagado / $this->total_a_pagar) * 100, 1);
    }

    public function estaEnMora(): bool
    {
        return $this->estado === 'mora';
    }

    // Genera código único para el crédito
    public static function generarCodigo(): string
    {
        $ultimo = static::latest()->first();
        $numero = $ultimo ? ((int) substr($ultimo->codigo, 3)) + 1 : 1;
        return 'CRD' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }

    // Genera las cuotas del crédito
    public function generarCuotas(): void
    {
        $fecha = $this->fecha_inicio->copy();
        for ($i = 1; $i <= $this->num_cuotas; $i++) {
            $this->cuotas()->create([
                'cliente_id'      => $this->cliente_id,
                'numero_cuota'    => $i,
                'valor_cuota'     => $this->valor_cuota,
                'saldo_cuota'     => $this->valor_cuota,
                'fecha_vencimiento' => $fecha->copy(),
                'estado'          => 'pendiente',
            ]);

            match ($this->frecuencia) {
                'diaria'    => $fecha->addDay(),
                'semanal'   => $fecha->addWeek(),
                'quincenal' => $fecha->addDays(15),
                'mensual'   => $fecha->addMonth(),
            };
        }
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['activo', 'al_dia', 'mora']);
    }

    public function scopeEnMora($query)
    {
        return $query->where('estado', 'mora');
    }
}