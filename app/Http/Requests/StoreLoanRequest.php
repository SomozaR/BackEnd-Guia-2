<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_solicitante' => ['required', 'string', 'max:255'],
            'libro_id' => ['required', 'integer', 'exists:libros,id'],
            'user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'fecha_hora_prestamo' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'libro_id.exists' => 'El libro especificado no existe.',
        ];
    }
}
