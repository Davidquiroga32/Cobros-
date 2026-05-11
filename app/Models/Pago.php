<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'recibo_numero', 'credito_id', 'cuota_id', 'cliente_id', 'cobrador_id',
        'monto_pagado', 'monto_mora', 'total_recibido', 'metodo_pago',
        'fecha_pago', 'observaciones', 'comprobante_foto',
        'latitud', 'longitud', 'es_pago_parcial',
    ];

    protected function casts(): array
    {
        return [
            'fecha_pago'      => 'datetime',
            'monto_pagado'    => 'float',
            'monto_mora'      => 'float',
            'total_recibido'  => 'float',
            'es_pago_parcial' => 'boolean',
            'latitud'         => 'float',
            'longitud'        => 'float',
        ];
    }

    // Relaciones
    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'credito_id');
    }

    public function cuota(): BelongsTo
    {
        return $this->belongsTo(Cuota::class, 'cuota_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cobrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cobrador_id');
    }

    // Genera número de recibo único
    public static function generarRecibo(): string
    {
        $ultimo = static::latest()->first();
        $numero = $ultimo ? ((int) substr($ultimo->recibo_numero, 3)) + 1 : 1;
        return 'REC' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}