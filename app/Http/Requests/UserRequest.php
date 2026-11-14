<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower($this->input('email'))]);
        }
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'first_name' => ['required', 'string', 'max:60', ValidationPatterns::rule(ValidationPatterns::NAME)],
            'last_name' => ['required', 'string', 'max:80', ValidationPatterns::rule(ValidationPatterns::NAME)],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', 'string', Rule::in(array_keys(User::ROLES))],
            // Przy edycji puste hasło oznacza „bez zmian”.
            'password' => [$user ? 'nullable' : 'required', 'string', 'max:72', 'confirmed', ValidationPatterns::rule(ValidationPatterns::PASSWORD)],
            'class_id' => ['nullable', 'required_if:role,'.User::ROLE_STUDENT, 'integer', Rule::exists('school_classes', 'id')],
            'children' => ['nullable', 'required_if:role,'.User::ROLE_PARENT, 'array', 'min:1'],
            'children.*' => ['integer', 'distinct', Rule::exists('users', 'id')->where('role', User::ROLE_STUDENT)],
        ];
    }
}
