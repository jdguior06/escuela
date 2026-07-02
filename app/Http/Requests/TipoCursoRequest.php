<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TipoCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
            'estado_curso' => ['required', 'string', 'in:activo,inactivo'],
            'duracion_horas' => ['required', 'integer', 'min:1'],
            'tipo_vehiculo_id' => ['required', 'integer', Rule::exists('tipo_vehiculo', 'id')],
        ];
    }
}
