<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CobradorEstado extends Model
{
    protected $table = 'cobradores_estado';

    protected $fillable = [
        'cobrador_id',
        'cn',
        'ubicacion_actual',
        'estado',
        'score',
        'caja_actual',
        'fecha_caja',
        'caja_inicial',
        'caja_final',
        'progreso_ruta',
        'ultima_sincronizacion',
        'pin_dispositivo',
        'version_app',
        'conectado',
        'latitud',
        'longitud',
    ];

    protected $casts = [
        'fecha_caja' => 'date',
        'ultima_sincronizacion' => 'datetime',
        'conectado' => 'boolean',
    ];

    public function cobrador()
    {
        return $this->belongsTo(User::class, 'cobrador_id');
    }
}