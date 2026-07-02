<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inscripcion_id' => ['required', 'integer', Rule::exists('inscripcion', 'id')],
            'metodo_id' => [
                'required',
                'integer',
                Rule::exists('metodo_pago', 'id')->where(fn ($query) => $query->whereIn('nombre', ['Efectivo', 'QR'])),
            ],
        ];
    }
}
