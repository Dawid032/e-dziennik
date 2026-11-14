<?php

namespace App\Http\Requests;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoteRequest extends FormRequest
{
    /** Wpisy dodają nauczyciele, a edytować można tylko własne. */
    public function authorize(): bool
    {
        if ($note = $this->route('note')) {
            return $note->teacher_id === $this->user()->id;
        }

        return $this->user()->isTeacher();
    }

    public function rules(): array
    {
        $rules = [
            'type' => ['required', 'string', Rule::in(array_keys(Note::TYPES))],
            'content' => ['required', 'string', 'max:1000'],
        ];

        // Uczeń musi należeć do klasy, której nauczyciel uczy lub jest wychowawcą.
        if (! $this->route('note')) {
            $rules['student_id'] = [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', User::ROLE_STUDENT)
                    ->whereIn('class_id', $this->user()->relatedClassIds()->all()),
            ];
        }

        return $rules;
    }
}
