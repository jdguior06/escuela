<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    protected $table = 'curso';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'fecha_inicio', 'fecha_fin', 'precio_final', 'estado_curso',
        'instructor_id', 'vehiculo_id', 'tipo_curso_id', 'franja_horaria_id', 'reservado_por',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'instructor_id');
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function tipoCurso(): BelongsTo
    {
        return $this->belongsTo(TipoCurso::class, 'tipo_curso_id');
    }

    public function franjaHoraria(): BelongsTo
    {
        return $this->belongsTo(FranjaHoraria::class, 'franja_horaria_id');
    }

    public function reservadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'reservado_por');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'curso_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'curso_id');
    }
}
