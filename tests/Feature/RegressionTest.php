<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testy usterek P1–P6 znalezionych podczas ręcznego sprawdzania aplikacji.
 * (P4, limit logowania, jest w AuthTest.)
 */
class RegressionTest extends TestCase
{
    use RefreshDatabase;

    /** P1: przy edycji hasło jest opcjonalne, więc pola nie mają gwiazdki. */
    public function test_password_fields_are_not_marked_required_when_editing(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $teacher = $this->makeUser('teacher');

        $this->get("/admin/users/{$teacher->id}/edit")->assertOk()
            ->assertDontSee('Nowe hasło <span class="req"', false)
            ->assertDontSee('Powtórz hasło <span class="req"', false);

        $this->get('/admin/users/create')->assertSee('Hasło <span class="req"', false);
    }

    /** P2: klasa i przedmiot mają własne komunikaty o złej nazwie. */
    public function test_class_and_subject_have_their_own_name_messages(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $this->post('/admin/classes', ['name' => 'A1', 'school_year' => '2026/2027'])
            ->assertSessionHasErrors(['name' => 'Nazwa klasy to cyfra 1–8 i wielka litera, np. 1A lub 3Ti.']);

        $this->post('/admin/subjects', ['name' => '123'])
            ->assertSessionHasErrors(['name' => 'Nazwa przedmiotu musi zaczynać się od litery, np. Sieci komputerowe.']);
    }

    /** P3: filtr historii nauczyciela obejmuje przedmioty klasy wychowawczej, ale nie cudze klasy. */
    public function test_history_filter_offers_homeroom_subjects(): void
    {
        $teacher = $this->makeUser('teacher');
        $other = $this->makeUser('teacher');
        $homeroom = $this->makeClass('1A', $teacher);
        $foreignClass = $this->makeClass('2B');
        $geography = $this->makeSubject('Geografia');
        $chemistry = $this->makeSubject('Chemia');
        $this->assignTeacher($homeroom, $geography, $other);
        $this->assignTeacher($foreignClass, $chemistry, $other);

        $this->makeGrade($this->makeUser('student', ['class_id' => $homeroom->id]), $geography, $other);
        $this->makeGrade($this->makeUser('student', ['class_id' => $foreignClass->id]), $chemistry, $other);

        $this->actingAs($teacher)->get('/history')
            ->assertSee('<option value="'.$geography->id.'"', false)
            ->assertDontSee('<option value="'.$chemistry->id.'"', false);

        $this->get('/history?subject='.$geography->id)->assertOk()->assertSee('Geografia');
    }

    /** P5: uwagi w danych demo mają różnych autorów, więc da się pokazać blokadę cudzej uwagi. */
    public function test_demo_notes_have_several_authors(): void
    {
        $this->seed();

        $this->assertGreaterThan(1, Note::distinct()->count('teacher_id'));

        $teacher = User::where('email', 'nauczyciel@wsb.pl')->first();
        $studentsOfTeacher = User::whereIn('class_id', $teacher->relatedClassIds())->pluck('id');
        $foreignNote = Note::where('teacher_id', '<>', $teacher->id)->whereIn('student_id', $studentsOfTeacher)->first();

        $this->assertNotNull($foreignNote);
        $this->actingAs($teacher)->get("/notes/{$foreignNote->id}/edit")->assertForbidden();
    }

    /** P6: panel wychowawcy pokazuje przedmiot, z którego uczeń ma ocenę bez przydziału. */
    public function test_homeroom_shows_subject_graded_without_assignment(): void
    {
        $teacher = $this->makeUser('teacher');
        $class = $this->makeClass('1A', $teacher);
        $student = $this->makeUser('student', ['class_id' => $class->id]);
        $this->makeGrade($student, $this->makeSubject('Chemia'), $this->makeUser('admin'), 5);

        $this->actingAs($teacher)->get("/teacher/homeroom/{$class->id}")->assertOk()->assertSee('Chemia');
    }
}
