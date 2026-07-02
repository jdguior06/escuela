<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuotaPlanPago extends Model
{
    protected $table = 'cuota_plan_pago';

    public $timestamps = false;

    protected $fillable = ['inscripcion_id', 'nro_cuota', 'monto', 'fecha_vencimiento', 'estado_cuota'];

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }
}
