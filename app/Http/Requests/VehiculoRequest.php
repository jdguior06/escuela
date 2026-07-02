<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'placa' => ['required', 'string', 'max:20', Rule::unique('vehiculo', 'placa')->ignore($this->route('vehiculo'))],
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'estado_vehiculo' => ['required', 'string', 'in:disponible,mantenimiento,inactivo'],
            'fecha_mantenimiento' => ['nullable', 'date'],
            'tipo_vehiculo_id' => ['required', 'integer', Rule::exists('tipo_vehiculo', 'id')],
        ];
    }
}
