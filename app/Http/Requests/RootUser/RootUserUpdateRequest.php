<?php

namespace App\Http\Requests\RootUser;

use Illuminate\Foundation\Http\FormRequest;

class RootUserUpdateRequest extends FormRequest
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
        $id = $this->route('id');

        return [
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:root_users,username,'.$id],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:root_users,email,'.$id,
        ];
    }
}
