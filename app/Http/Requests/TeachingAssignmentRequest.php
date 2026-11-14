<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeachingAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id'),
                Rule::unique('class_subject_teacher', 'subject_id')
                    ->where('class_id', $this->input('class_id'))
                    ->ignore($this->route('assignment')),
            ],
            'teacher_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_TEACHER)],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.unique' => 'Ta klasa ma już nauczyciela tego przedmiotu.',
        ];
    }
}
