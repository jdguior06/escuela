<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['nombre', 'apellido', 'fecha_nacimiento', 'genero', 'nro_documento', 'correo', 'telefono', 'direccion', 'password', 'estado_usuario', 'rol_id'])]
#[Hidden(['password', 'remember_token'])]
class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'usuario';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    public function routeNotificationForMail(): string
    {
        return $this->correo;
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'fecha_nacimiento' => 'date',
        ];
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
}
