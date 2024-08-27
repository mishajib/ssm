<?php

namespace App\Http\Requests\API\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::default()],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ];

        if ($this->isMethod('PUT')) {
            $rules['email'] = ['required', 'email', 'unique:users,id,:id'];
            $rules['password'] = ['nullable', 'string', Password::default()];
            $rules['password_confirmation'] = ['nullable', 'string', 'same:password'];
        }

        return $rules;
    }


    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        if ($this->get('password')) {
            $data['password'] = bcrypt($this->get('password'));
            unset($data['password_confirmation']);
        }

        return $data;
    }
}
