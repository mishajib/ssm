<?php

namespace App\Http\Requests\API\V1\Student;

use Illuminate\Foundation\Http\FormRequest;

class SessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:users,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'repeat' => 'boolean',
        ];
    }

    /*
     * Get the validation attributes that apply to the request.
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'student',
            'start_time' => 'start time',
            'end_time' => 'end time',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'start_time.date_format' => 'The start time does not match the format H:i (24-hour).',
            'end_time.date_format' => 'The end time does not match the format H:i (24-hour).',
            'end_time.after' => 'The end time must be a time after start time.',
        ];
    }
}
