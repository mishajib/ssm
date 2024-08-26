<?php

namespace App\Http\Requests\API\V1\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'bail|required|string|max:255',
            'last_name' => 'bail|required|string|max:255',
            'username' => 'bail|required|alpha_num|max:255|unique:' . User::class,
            'email' => 'bail|required|string|lowercase|email|max:255|unique:' . User::class,
            'customer_type' => 'bail|required|exists:customer_types,id',
            'phone' => 'bail|required|string|max:10',
            // strong password
            'password' => [
                'bail',
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols()
            ],
            'password_confirmation' => [
                'bail',
                'required',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ];
    }
}
