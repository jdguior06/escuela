<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inscripcion extends Model
{
    protected $table = 'inscripcion';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'fecha_inscripcion', 'estado_inscripcion', 'monto_total',
        'estudiante_id', 'plan_pago_id', 'curso_id',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'estudiante_id');
    }

    public function planPago(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_pago_id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'inscripcion_id');
    }

    public function controlCertificacion(): HasMany
    {
        return $this->hasMany(ControlCertificacion::class, 'inscripcion_id');
    }

    public function cuotasPlanPago(): HasMany
    {
        return $this->hasMany(CuotaPlanPago::class, 'inscripcion_id');
    }
}
