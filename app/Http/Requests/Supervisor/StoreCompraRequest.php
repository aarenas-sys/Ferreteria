<?php

namespace App\Http\Requests\Supervisor;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'tipo_pago' => ['required', 'in:contado,credito,transferencia'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'exists:productos,id'],
            'detalles.*.cantidad_solicitada' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_compra' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'El proveedor es obligatorio.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'tipo_pago.required' => 'El tipo de pago es obligatorio.',
            'tipo_pago.in' => 'El tipo de pago seleccionado no es válido.',
            'detalles.required' => 'Debe agregar al menos un producto a la compra.',
            'detalles.array' => 'Los detalles de la compra deben enviarse correctamente.',
            'detalles.min' => 'Debe agregar al menos un producto a la compra.',
            'detalles.*.producto_id.required' => 'El producto es obligatorio.',
            'detalles.*.producto_id.exists' => 'El producto seleccionado no existe.',
            'detalles.*.cantidad_solicitada.required' => 'La cantidad solicitada es obligatoria.',
            'detalles.*.cantidad_solicitada.integer' => 'La cantidad solicitada debe ser un número entero.',
            'detalles.*.cantidad_solicitada.min' => 'La cantidad solicitada debe ser mayor a cero.',
            'detalles.*.precio_compra.required' => 'El precio de compra es obligatorio.',
            'detalles.*.precio_compra.numeric' => 'El precio de compra debe ser un número válido.',
            'detalles.*.precio_compra.min' => 'El precio de compra debe ser mayor a cero.',
        ];
    }
}
