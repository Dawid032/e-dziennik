<?php

return [

    'accepted' => 'Pole :attribute musi zostać zaakceptowane.',
    'after' => 'Pole :attribute musi być datą późniejszą niż :date.',
    'alpha' => 'Pole :attribute może zawierać tylko litery.',
    'alpha_num' => 'Pole :attribute może zawierać tylko litery i cyfry.',
    'array' => 'Pole :attribute musi być listą.',
    'before' => 'Pole :attribute musi być datą wcześniejszą niż :date.',
    'between' => [
        'array' => 'Pole :attribute musi zawierać od :min do :max elementów.',
        'file' => 'Pole :attribute musi mieć od :min do :max KB.',
        'numeric' => 'Pole :attribute musi mieć wartość od :min do :max.',
        'string' => 'Pole :attribute musi mieć od :min do :max znaków.',
    ],
    'boolean' => 'Pole :attribute musi mieć wartość prawda lub fałsz.',
    'confirmed' => 'Potwierdzenie pola :attribute nie zgadza się.',
    'current_password' => 'Podane hasło jest nieprawidłowe.',
    'date' => 'Pole :attribute musi być poprawną datą.',
    'different' => 'Pola :attribute i :other muszą się różnić.',
    'digits' => 'Pole :attribute musi składać się z :digits cyfr.',
    'distinct' => 'Pole :attribute zawiera powtórzoną wartość.',
    'email' => 'Pole :attribute musi być poprawnym adresem e-mail.',
    'exists' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
    'filled' => 'Pole :attribute musi być wypełnione.',
    'gt' => [
        'numeric' => 'Pole :attribute musi być większe niż :value.',
    ],
    'gte' => [
        'numeric' => 'Pole :attribute musi być większe lub równe :value.',
    ],
    'in' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
    'integer' => 'Pole :attribute musi być liczbą całkowitą.',
    'lt' => [
        'numeric' => 'Pole :attribute musi być mniejsze niż :value.',
    ],
    'lte' => [
        'numeric' => 'Pole :attribute musi być mniejsze lub równe :value.',
    ],
    'max' => [
        'array' => 'Pole :attribute może zawierać najwyżej :max elementów.',
        'file' => 'Pole :attribute może mieć najwyżej :max KB.',
        'numeric' => 'Pole :attribute nie może być większe niż :max.',
        'string' => 'Pole :attribute może mieć najwyżej :max znaków.',
    ],
    'min' => [
        'array' => 'Pole :attribute musi zawierać co najmniej :min elementów.',
        'file' => 'Pole :attribute musi mieć co najmniej :min KB.',
        'numeric' => 'Pole :attribute musi być równe co najmniej :min.',
        'string' => 'Pole :attribute musi mieć co najmniej :min znaków.',
    ],
    'not_in' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
    'numeric' => 'Pole :attribute musi być liczbą.',
    'present' => 'Pole :attribute musi być obecne.',
    'regex' => 'Pole :attribute ma nieprawidłowy format.',
    'required' => 'Pole :attribute jest wymagane.',
    'required_if' => 'Pole :attribute jest wymagane, gdy :other to :value.',
    'required_unless' => 'Pole :attribute jest wymagane, chyba że :other to :values.',
    'required_with' => 'Pole :attribute jest wymagane, gdy podano :values.',
    'required_without' => 'Pole :attribute jest wymagane, gdy nie podano :values.',
    'same' => 'Pola :attribute i :other muszą być takie same.',
    'size' => [
        'array' => 'Pole :attribute musi zawierać :size elementów.',
        'file' => 'Pole :attribute musi mieć :size KB.',
        'numeric' => 'Pole :attribute musi mieć wartość :size.',
        'string' => 'Pole :attribute musi mieć :size znaków.',
    ],
    'string' => 'Pole :attribute musi być tekstem.',
    'unique' => 'Taka wartość pola :attribute już istnieje.',
    'url' => 'Pole :attribute musi być poprawnym adresem URL.',

    // Bardziej zrozumiałe komunikaty dla pojedynczych pól.
    'custom' => [
        'first_name' => ['regex' => 'Imię musi zaczynać się wielką literą i zawierać tylko litery.'],
        'last_name' => ['regex' => 'Nazwisko musi zaczynać się wielką literą i zawierać tylko litery.'],
        'password' => ['regex' => 'Hasło musi mieć co najmniej 8 znaków, w tym literę i cyfrę.'],
        'school_year' => ['regex' => 'Rok szkolny podaj w formacie 2026/2027.'],
    ],

    // Polskie nazwy pól używane w komunikatach.
    'attributes' => [
        'first_name' => 'imię',
        'last_name' => 'nazwisko',
        'email' => 'e-mail',
        'password' => 'hasło',
        'current_password' => 'obecne hasło',
        'role' => 'rola',
        'class_id' => 'klasa',
        'children' => 'dzieci',
        'children.*' => 'dziecko',
        'name' => 'nazwa',
        'school_year' => 'rok szkolny',
        'homeroom_teacher_id' => 'wychowawca',
        'subject_id' => 'przedmiot',
        'teacher_id' => 'nauczyciel',
        'student_id' => 'uczeń',
        'value' => 'ocena',
        'values' => 'oceny',
        'values.*' => 'ocena',
        'weight' => 'waga',
        'category' => 'kategoria',
        'description' => 'opis',
        'type' => 'rodzaj',
        'content' => 'treść',
    ],

    // Polskie nazwy wartości używane w regule required_if.
    'values' => [
        'role' => [
            'student' => 'uczeń',
            'parent' => 'rodzic',
        ],
    ],

];
