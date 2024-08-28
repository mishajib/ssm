<?php

namespace App\Http\Requests\API\V1\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudentAvailabilityRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            /** @default ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"] */
            'week_days' => 'required|array',
            'week_days.*' => 'required|string',
        ];
    }


    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'week_days.required' => 'Week days are required!',
            'week_days.array' => 'Week days must be an array!',
            'week_days.*.required' => 'Week day availability is required!',
            'week_days.*.string' => 'Week day availability must be a string!',
        ];
    }
}
