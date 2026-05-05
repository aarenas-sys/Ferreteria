<?php

namespace App\Http\Requests\Bodeguero;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'detalles' => ['required', 'array'],
            'detalles.*.id' => ['required', 'integer', 'exists:compra_detalles,id'],
            'detalles.*.cantidad_recibida' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'detalles.required' => 'Debe actualizar al menos un detalle de recepción.',
            'detalles.array' => 'Los detalles deben enviarse correctamente.',
            'detalles.*.id.required' => 'El detalle de compra es obligatorio.',
            'detalles.*.id.exists' => 'El detalle de compra no existe.',
            'detalles.*.cantidad_recibida.required' => 'La cantidad recibida es obligatoria.',
            'detalles.*.cantidad_recibida.integer' => 'La cantidad recibida debe ser un número entero.',
            'detalles.*.cantidad_recibida.min' => 'La cantidad recibida no puede ser negativa.',
        ];
    }
}
