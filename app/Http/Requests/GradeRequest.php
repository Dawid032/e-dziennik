<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeRequest extends FormRequest
{
    /** Formularz jest sprawdzany dopiero, gdy użytkownik ma prawo oceniać tę klasę i przedmiot. */
    public function authorize(): bool
    {
        if ($grade = $this->route('grade')) {
            return $this->user()->canGrade($grade->student->class_id, $grade->subject_id);
        }

        return $this->user()->canGrade($this->route('class')->id, $this->route('subject')->id);
    }

    public function rules(): array
    {
        $rules = [
            'value' => ['required', 'integer', 'between:1,6'],
            'weight' => ['required', 'integer', 'between:1,5'],
            'category' => ['required', 'string', Rule::in(config('dziennik.grade_categories'))],
            'description' => ['nullable', 'string', 'max:255'],
        ];

        // Przy edycji uczeń jest już znany, przy dodawaniu musi należeć do tej klasy.
        if (! $this->route('grade')) {
            $rules['student_id'] = [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', User::ROLE_STUDENT)
                    ->where('class_id', $this->route('class')->id),
            ];
        }

        return $rules;
    }
}
