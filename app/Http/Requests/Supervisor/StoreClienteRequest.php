<?php

namespace App\Http\Requests\Supervisor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'supervisor';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'primer_nombre' => 'required|string|max:50',
            'segundo_nombre' => 'nullable|string|max:50',
            'primer_apellido' => 'required|string|max:50',
            'segundo_apellido' => 'nullable|string|max:50',
            'documento' => 'required|string|max:20|unique:clientes,documento',
            'email' => 'nullable|email|max:100|unique:clientes,email',
            'telefono' => 'required|string|max:20',
            'direccion' => 'required|string|max:255',
            'cupo_credito' => 'nullable|numeric|min:0|max:1000000',
            'estado_credito' => 'required|string|in:activo,bloqueado',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cupo_credito.max' => 'El cupo de crédito no puede superar $1.000.000',
            'estado_credito.in' => 'El estado de crédito debe ser activo o bloqueado.',
        ];
    }
}
