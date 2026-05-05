<?php

namespace App\Http\Requests\Cajero;

use Illuminate\Foundation\Http\FormRequest;

class ArqueoCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'cajero';
    }

    public function rules(): array
    {
        return [
            'monto_real' => 'required|numeric|min:0',
        ];
    }
}
