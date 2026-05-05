<?php

namespace App\Http\Requests\Cajero;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDevolucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'venta_id' => ['required', 'integer', 'exists:ventas,id'],
            'tipo_devolucion' => ['required', Rule::in(['efectivo', 'transferencia'])],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'productos.*.cantidad' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = collect($this->input('productos', []));
            $validItems = $items->filter(fn ($item) => isset($item['cantidad']) && (int) $item['cantidad'] > 0);

            if ($validItems->isEmpty()) {
                $validator->errors()->add('productos', 'Debes seleccionar al menos un producto con cantidad mayor a cero.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'venta_id.required' => 'La venta es obligatoria.',
            'venta_id.exists' => 'La venta seleccionada no existe.',
            'tipo_devolucion.required' => 'Selecciona el tipo de devolución.',
            'tipo_devolucion.in' => 'El tipo de devolución no es válido.',
            'productos.required' => 'Debes seleccionar productos para devolver.',
            'productos.array' => 'Los productos deben estar en un formato válido.',
            'productos.*.producto_id.required' => 'El producto es obligatorio.',
            'productos.*.producto_id.exists' => 'El producto seleccionado no es válido.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad debe ser cero o mayor.',
        ];
    }
}
