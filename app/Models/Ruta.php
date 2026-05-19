<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruta extends Model
{
    protected $table = 'rutas';

    protected $fillable = [
        'cobrador_id', 'caja_id', 'fecha',
        'total_paradas', 'paradas_completadas', 'estado',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    // ── Relaciones ────────────────────────────────────────────
    public function cobrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cobrador_id');
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function paradas(): HasMany
    {
        return $this->hasMany(RutaParada::class, 'ruta_id')->orderBy('orden');
    }

    // ── Helpers ───────────────────────────────────────────────
    public function porcentajeProgreso(): int
    {
        if ($this->total_paradas === 0) return 0;
        return (int) round(($this->paradas_completadas / $this->total_paradas) * 100);
    }

    /**
     * Recalcula contadores de paradas desde la BD.
     * Llamar cada vez que una parada cambia de estado.
     */
    public function recalcularProgreso(): void
    {
        $total       = $this->paradas()->count();
        $completadas = $this->paradas()->where('estado', 'visitado')->count();

        $this->update([
            'total_paradas'       => $total,
            'paradas_completadas' => $completadas,
            'estado'              => $total > 0 && $completadas >= $total ? 'completada' : 'en_curso',
        ]);
    }

    /**
     * Crea la ruta del día para un cobrador desde sus cuotas pendientes.
     */
    public static function generarDesde(int $cobradorId, ?int $cajaId = null): self
    {
        $ruta = static::firstOrCreate(
            ['cobrador_id' => $cobradorId, 'fecha' => today()],
            ['caja_id' => $cajaId, 'estado' => 'pendiente']
        );

        // Sólo poblar paradas si es nueva
        if ($ruta->wasRecentlyCreated) {
            $cuotas = \App\Models\Cuota::with('cliente')
                ->whereHas('credito', fn ($q) => $q->where('cobrador_id', $cobradorId))
                ->whereDate('fecha_vencimiento', today())
                ->whereIn('estado', ['pendiente', 'parcial', 'vencida'])
                ->orderBy('fecha_vencimiento')
                ->get();

            $orden = 1;
            foreach ($cuotas as $cuota) {
                $ruta->paradas()->create([
                    'cliente_id'      => $cuota->cliente_id,
                    'cuota_id'        => $cuota->id,
                    'orden'           => $orden++,
                    'monto_esperado'  => $cuota->saldo_cuota,
                ]);
            }

            $ruta->update(['total_paradas' => $cuotas->count()]);
        }

        return $ruta;
    }
}