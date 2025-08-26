<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
             'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get the custom validation messages for the request.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O  nome é obrigatório.',
            'name.string' => 'O  nome deve ser uma string.',
            'name.max' => 'O  nome não deve exceder 255 caracteres.',
            'email.required' => 'O  email é obrigatório.',
            'email.email' => 'O  email deve ser um endereço de email válido.',
            'email.max' => 'O  email não deve exceder 255 caracteres.',
            'email.unique' => 'O  email já está em uso.',
            'password.required' => 'A  senha é obrigatória.',
            'password.min' => 'A  senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
        ];
    }
}
