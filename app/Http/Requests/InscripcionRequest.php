<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InscripcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'plan_pago_id' => ['required', 'integer', Rule::exists('plan_pago', 'id')],
            'reserva_id' => ['nullable', 'integer', Rule::exists('reserva', 'id')],
            'curso_id' => ['required_without:reserva_id', 'nullable', 'integer', Rule::exists('curso', 'id')],
        ];

        /** @var Usuario $usuario */
        $usuario = $this->user();

        if ($usuario->hasRole(['Secretaria', 'Propietario']) && ! $this->filled('reserva_id')) {
            $rules['estudiante_id'] = ['required', 'integer', Rule::exists('usuario', 'id')];
        }

        return $rules;
    }
}
