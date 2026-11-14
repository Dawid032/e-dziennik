<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BulkGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canGrade($this->route('class')->id, $this->route('subject')->id);
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', Rule::in(config('dziennik.grade_categories'))],
            'weight' => ['required', 'integer', 'between:1,5'],
            'description' => ['nullable', 'string', 'max:255'],
            'values' => ['required', 'array'],
            'values.*' => ['nullable', 'integer', 'between:1,6'],
        ];
    }

    /** Sprawdzenia, których nie da się zapisać zwykłą regułą. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $values = collect($this->input('values'))->filter(fn ($value) => $value !== null);

                if ($validator->errors()->has('values') || $validator->errors()->has('values.*')) {
                    return;
                }

                if ($values->isEmpty()) {
                    $validator->errors()->add('values', 'Wpisz ocenę co najmniej jednemu uczniowi.');

                    return;
                }

                $roster = $this->route('class')->students()->pluck('id');

                if ($values->keys()->map(fn ($id) => (int) $id)->diff($roster)->isNotEmpty()) {
                    $validator->errors()->add('values', 'Formularz zawiera ucznia spoza tej klasy.');
                }
            },
        ];
    }
}
