<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Responses\ApiResponse;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user'); 

        return [
            'name' => [
                'sometimes',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s]+$/' 
            ],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)->whereNull('deleted_at')
            ],
            'password' => [
                'sometimes',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/' 
            ],
            'is_admin' => [
                'sometimes',
                'boolean'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.min' => 'O nome deve ter pelo menos 2 caracteres',
            'name.max' => 'O nome não pode ter mais de 255 caracteres',
            'name.regex' => 'O nome deve conter apenas letras e espaços',
            
            'email.email' => 'O e-mail deve ter um formato válido',
            'email.unique' => 'Este e-mail já está em uso',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres',
            
            'password.min' => 'A senha deve ter pelo menos 8 caracteres',
            'password.confirmed' => 'A confirmação da senha não confere',
            'password.regex' => 'A senha deve conter pelo menos: 1 letra minúscula, 1 maiúscula, 1 número e 1 caractere especial',
            
            'is_admin.boolean' => 'O campo is_admin deve ser verdadeiro ou falso',
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
            'is_admin' => 'administrador',
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
        // Limpar e formatar dados apenas se estiverem presentes
        $data = [];
        
        if ($this->has('name')) {
            $data['name'] = trim($this->name);
        }
        
        if ($this->has('email')) {
            $data['email'] = strtolower(trim($this->email));
        }
        
        if ($this->has('is_admin')) {
            $data['is_admin'] = $this->boolean('is_admin');
        }

        $this->merge($data);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $currentUser = auth()->user();
            $targetUserId = $this->route('user');
            
            if ($this->has('is_admin')) {
                if (!$currentUser->isAdmin()) {
                    $validator->errors()->add('is_admin', 'Apenas administradores podem alterar o status de administrador');
                }
                
                if ($currentUser->id == $targetUserId && $currentUser->isAdmin() && !$this->boolean('is_admin')) {
                    $validator->errors()->add('is_admin', 'Você não pode remover seu próprio status de administrador');
                }
            }
        });
    }
}