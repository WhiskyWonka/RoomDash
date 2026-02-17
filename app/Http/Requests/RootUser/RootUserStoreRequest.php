<?php

namespace App\Http\Requests\RootUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RootUserStoreRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:root_users,username'],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => [
                'required',
                'confirmed', // Requiere que exista un campo password_confirmation
                Password::min(8)
                    ->letters()      // Al menos una letra
                    ->mixedCase()    // Mayúsculas y minúsculas
                    ->numbers()      // Al menos un número
                    ->symbols()      // Al menos un carácter especial (@, $, !, etc.)
                    ->uncompromised(), // ¡Súper útil! Verifica si la contraseña ha sido filtrada en internet
            ],
            'email' => 'required|email|max:255|unique:root_users,email',
        ];
    }
}
