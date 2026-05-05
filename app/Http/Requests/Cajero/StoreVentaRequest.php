<?php

namespace App\Http\Requests\Cajero;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'cajero';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'cliente_id' => 'nullable|exists:clientes,id',
            'tipo_venta' => 'required|in:contado,credito',
            'descuento_id' => 'nullable|exists:discounts,id',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ];

        // Si es venta a crédito, agregar validaciones adicionales
        if ($this->input('tipo_venta') === 'credito') {
            $rules['cliente_id'] = 'required|exists:clientes,id';
            $rules['fecha_vencimiento'] = 'required|date|after:today';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        // Las validaciones específicas de cliente se manejan en el controller
    }

    public function messages(): array
    {
        return [
            'tipo_venta.required' => 'El tipo de venta es obligatorio.',
            'tipo_venta.in' => 'El tipo de venta debe ser contado o crédito.',
            'cliente_id.required' => 'El cliente es obligatorio para ventas a crédito.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'fecha_vencimiento.required_if' => 'La fecha de vencimiento es obligatoria para ventas a crédito.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
            'productos.required' => 'Debe seleccionar al menos un producto.',
            'productos.array' => 'Los productos deben ser un arreglo.',
            'productos.min' => 'Debe seleccionar al menos un producto.',
            'productos.*.id.required' => 'El ID del producto es obligatorio.',
            'productos.*.id.exists' => 'El producto seleccionado no existe.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'descuento_id.exists' => 'El descuento seleccionado no es válido.',
        ];
    }
}
