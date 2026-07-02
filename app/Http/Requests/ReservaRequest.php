<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'curso_id' => ['required', 'integer', Rule::exists('curso', 'id')],
        ];

        /** @var Usuario $usuario */
        $usuario = $this->user();

        if ($usuario->hasRole(['Secretaria', 'Propietario'])) {
            $rules['usuario_id'] = ['required', 'integer', Rule::exists('usuario', 'id')];
        }

        return $rules;
    }
}
