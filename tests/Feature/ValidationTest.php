<?php

namespace Tests\Feature;

use App\Support\ValidationPatterns as P;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_forms_are_rejected_by_the_server(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['email', 'password']);

        $teacher = $this->makeUser('teacher');
        $admin = $this->makeUser('admin');
        $class = $this->makeClass('1A', $teacher);
        $subject = $this->makeSubject();
        $student = $this->makeUser('student', ['class_id' => $class->id]);
        $this->assignTeacher($class, $subject, $teacher);
        $grade = $this->makeGrade($student, $subject, $teacher);

        $this->actingAs($admin);
        $this->post('/admin/users', [])->assertSessionHasErrors(['first_name', 'last_name', 'email', 'role', 'password']);
        $this->put("/admin/users/{$teacher->id}", [])->assertSessionHasErrors(['first_name', 'last_name', 'email', 'role']);
        $this->post('/admin/classes', [])->assertSessionHasErrors(['name', 'school_year']);
        $this->put("/admin/classes/{$class->id}", [])->assertSessionHasErrors(['name', 'school_year']);
        $this->post('/admin/subjects', [])->assertSessionHasErrors('name');
        $this->put("/admin/subjects/{$subject->id}", [])->assertSessionHasErrors('name');
        $this->post('/admin/assignments', [])->assertSessionHasErrors(['class_id', 'subject_id', 'teacher_id']);
        $this->get('/gradebook/open')->assertSessionHasErrors(['class_id', 'subject_id']);

        $this->actingAs($teacher);
        $this->post("/gradebook/{$class->id}/{$subject->id}/grades", [])->assertSessionHasErrors(['student_id', 'value', 'weight', 'category']);
        $this->post("/gradebook/{$class->id}/{$subject->id}/bulk", [])->assertSessionHasErrors(['category', 'weight', 'values']);
        $this->put("/grades/{$grade->id}", [])->assertSessionHasErrors(['value', 'weight', 'category']);
        $this->post('/notes', [])->assertSessionHasErrors(['student_id', 'type', 'content']);
        $this->put('/account/password', [])->assertSessionHasErrors(['current_password', 'password']);
    }

    public function test_messages_are_in_polish_and_input_is_kept(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $this->from('/admin/users/create')->post('/admin/users', [
            'first_name' => 'anna',
            'email' => 'zly-adres',
        ])->assertRedirect('/admin/users/create')->assertSessionHasInput('first_name', 'anna');

        $this->get('/admin/users/create')
            ->assertSee('Imię musi zaczynać się wielką literą')
            ->assertSee('Pole nazwisko jest wymagane')
            ->assertSee('Pole e-mail musi być poprawnym adresem e-mail')
            ->assertSee('value="anna"', false);
    }

    public static function regexCases(): array
    {
        return [
            'imię OK' => [P::NAME, 'Anna', true],
            'nazwisko z myślnikiem OK' => [P::NAME, 'Nowak-Kowalska', true],
            'polskie znaki OK' => [P::NAME, 'Łukasz', true],
            'imię małą literą' => [P::NAME, 'anna', false],
            'imię z cyframi' => [P::NAME, 'Jan123', false],
            'hasło OK' => [P::PASSWORD, 'haslo123', true],
            'hasło bez cyfry' => [P::PASSWORD, 'abcdefgh', false],
            'hasło za krótkie' => [P::PASSWORD, '1234567', false],
            'klasa OK' => [P::CLASS_NAME, '1A', true],
            'klasa dłuższa OK' => [P::CLASS_NAME, '3Ti', true],
            'klasa odwrotnie' => [P::CLASS_NAME, 'A1', false],
            'klasa spoza zakresu' => [P::CLASS_NAME, '9Z', false],
            'rok OK' => [P::SCHOOL_YEAR, '2026/2027', true],
            'rok skrócony' => [P::SCHOOL_YEAR, '26/27', false],
            'przedmiot OK' => [P::SUBJECT_NAME, 'Sieci komputerowe', true],
            'przedmiot z nawiasem OK' => [P::SUBJECT_NAME, 'Informatyka (rozszerzona)', true],
            'przedmiot z cyfr' => [P::SUBJECT_NAME, '123', false],
        ];
    }

    #[DataProvider('regexCases')]
    public function test_patterns(string $pattern, string $value, bool $valid): void
    {
        $validator = Validator::make(['field' => $value], ['field' => [P::rule($pattern)]]);

        $this->assertSame($valid, $validator->passes());
    }

    public function test_school_year_must_be_consecutive(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $this->post('/admin/classes', ['name' => '1A', 'school_year' => '2026/2028'])->assertSessionHasErrors('school_year');
        $this->post('/admin/classes', ['name' => '1A', 'school_year' => '2026/2027'])->assertSessionHasNoErrors();
    }
}
