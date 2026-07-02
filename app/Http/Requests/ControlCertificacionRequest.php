<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ControlCertificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inscripcion_id' => [
                'required',
                'integer',
                Rule::exists('inscripcion', 'id'),
                Rule::unique('control_certificacion', 'inscripcion_id'),
            ],
            'nota' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
