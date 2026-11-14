<?php

namespace App\Http\Requests;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:80',
                ValidationPatterns::rule(ValidationPatterns::SUBJECT_NAME),
                Rule::unique('subjects', 'name')->ignore($this->route('subject')),
            ],
        ];
    }
}
