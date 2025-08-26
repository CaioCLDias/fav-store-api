<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Responses\ApiResponse;

class StoreUserRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s]+$/' 
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns', 
                'max:255',
                'unique:users,email'
            ],
            'is_admin' => [
                'required',
                'boolean',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/' 
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.string' => 'O nome deve ser um texto válido',
            'name.min' => 'O nome deve ter pelo menos 2 caracteres',
            'name.max' => 'O nome não pode ter mais de 255 caracteres',
            'name.regex' => 'O nome deve conter apenas letras e espaços',

            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'O e-mail deve ter um formato válido',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres',
            'email.unique' => 'Este e-mail já está sendo usado por outro usuário',

            'is_admin.required' => 'O campo "is_admin" é obrigatório',
            'is_admin.boolean' => 'O campo "is_admin" deve ser verdadeiro ou falso',

            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres',
            'password.max' => 'A senha não pode ter mais de 255 caracteres',
            'password.confirmed' => 'A confirmação da senha não confere',
            'password.regex' => 'A senha deve conter pelo menos: 1 letra minúscula, 1 número e 1 caractere especial',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'password' => 'senha',
            'password_confirmation' => 'confirmação da senha',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::validationError(
                $validator->errors()->toArray(),
                'Os dados fornecidos são inválidos'
            )
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
        ]);
    }
}
