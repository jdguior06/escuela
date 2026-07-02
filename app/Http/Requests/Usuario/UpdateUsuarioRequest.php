<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'genero' => ['nullable', 'string', 'in:M,F'],
            'nro_documento' => ['required', 'string', 'max:20', Rule::unique('usuario', 'nro_documento')->ignore($this->route('usuario'))],
            'correo' => ['required', 'string', 'lowercase', 'email', 'max:150', Rule::unique('usuario', 'correo')->ignore($this->route('usuario'))],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado_usuario' => ['required', 'string', 'in:activo,inactivo'],
            'rol_id' => ['required', 'integer', Rule::exists('rol', 'id')],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }
}
