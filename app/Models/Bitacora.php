<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    protected $table = 'bitacora';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = null;

    protected $fillable = ['usuario_id', 'tipo_evento', 'recurso', 'ip', 'user_agent'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
