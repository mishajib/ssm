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
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
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
}
