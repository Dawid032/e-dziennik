<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\ValidationPatterns;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolClassRequest extends FormRequest
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
                'max:5',
                ValidationPatterns::rule(ValidationPatterns::CLASS_NAME),
                Rule::unique('school_classes', 'name')
                    ->where('school_year', $this->input('school_year'))
                    ->ignore($this->route('class')),
            ],
            'school_year' => [
                'required',
                'string',
                'max:9',
                ValidationPatterns::rule(ValidationPatterns::SCHOOL_YEAR),
                function (string $attribute, mixed $value, Closure $fail) {
                    if (preg_match('~'.ValidationPatterns::SCHOOL_YEAR.'~', (string) $value, $years)
                        && (int) $years[2] !== (int) $years[1] + 1) {
                        $fail('Drugi rok musi być o 1 większy od pierwszego.');
                    }
                },
            ],
            'homeroom_teacher_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_TEACHER)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Taka klasa już istnieje w tym roku szkolnym.',
            'name.regex' => 'Nazwa klasy to cyfra 1–8 i wielka litera, np. 1A lub 3Ti.',
        ];
    }
}
