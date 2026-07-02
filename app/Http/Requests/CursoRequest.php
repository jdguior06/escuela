<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'precio_final' => ['required', 'numeric', 'min:0'],
            'instructor_id' => ['required', 'integer', Rule::exists('usuario', 'id')],
            'vehiculo_id' => ['required', 'integer', Rule::exists('vehiculo', 'id')],
            'tipo_curso_id' => ['required', 'integer', Rule::exists('tipo_curso', 'id')],
            'franja_horaria_id' => ['required', 'integer', Rule::exists('franja_horaria', 'id')],
            'estado_curso' => ['sometimes', 'string', 'in:disponible,reservado,inscrito,cancelado'],
        ];
    }
}
