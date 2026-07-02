<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pago';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /** Mapeo del campo numérico `Estado` que envía el callback de PagoFácil. */
    const ESTADOS = [
        1 => 'pendiente',
        2 => 'pagado',
        3 => 'revertido',
        4 => 'anulado',
    ];

    protected $fillable = [
        'fecha', 'monto', 'nro_cuota', 'id_transaccion', 'nro_pedido',
        'estado_pago', 'correo_notificacion', 'notificado',
        'usuario_id', 'metodo_id', 'inscripcion_id',
    ];

    protected $casts = [
        'notificado' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function metodoPago(): BelongsTo
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_id');
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }
}
