<?php

namespace App\Http\Requests\Cajero;

use Illuminate\Foundation\Http\FormRequest;

class AbrirCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'cajero';
    }

    public function rules(): array
    {
        return [
            'monto_inicial' => 'required|numeric|min:0',
        ];
    }
}
