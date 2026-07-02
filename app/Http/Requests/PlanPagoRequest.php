<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:50'],
            'numero_cuotas' => ['required', 'integer', 'min:1'],
            'estado' => ['required', 'string', 'in:activo,inactivo'],
        ];
    }
}
