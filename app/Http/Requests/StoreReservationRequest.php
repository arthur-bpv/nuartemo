<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // As rotas devem exigir autenticação.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'key_id' => ['required', 'integer', 'exists:keys,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'purpose' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'key_id.required' => 'Selecione uma chave.',
            'key_id.exists' => 'A chave selecionada não existe.',
            'starts_at.required' => 'Informe o início da reserva.',
            'starts_at.after' => 'O início da reserva deve ser no futuro.',
            'ends_at.required' => 'Informe o fim da reserva.',
            'ends_at.after' => 'O fim deve ser depois do início.',
            'purpose.required' => 'Informe a finalidade da reserva.',
        ];
    }
}