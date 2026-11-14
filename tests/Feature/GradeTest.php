<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\GradeHistory;
use App\Models\Note;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradeAverageCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private SchoolClass $class;

    private Subject $subject;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = $this->makeUser('teacher');
        $this->class = $this->makeClass();
        $this->subject = $this->makeSubject();
        $this->student = $this->makeUser('student', ['class_id' => $this->class->id]);
        $this->assignTeacher($this->class, $this->subject, $this->teacher);
    }

    private function gradesUrl(string $suffix = 'grades'): string
    {
        return "/gradebook/{$this->class->id}/{$this->subject->id}/{$suffix}";
    }

    private function gradeData(array $overrides = []): array
    {
        return $overrides + ['student_id' => $this->student->id, 'value' => 5, 'weight' => 3, 'category' => 'Kartkówka', 'description' => 'Temat'];
    }

    public function test_teacher_adds_grade_in_own_assignment(): void
    {
        $this->actingAs($this->teacher)->post($this->gradesUrl(), $this->gradeData())->assertSessionHasNoErrors();

        $this->assertDatabaseHas('grades', ['student_id' => $this->student->id, 'value' => 5, 'teacher_id' => $this->teacher->id]);
    }

    public function test_admin_grades_any_class_and_subject(): void
    {
        $admin = $this->makeUser('admin');
        $otherSubject = $this->makeSubject('Fizyka');

        $this->actingAs($admin)
            ->post("/gradebook/{$this->class->id}/{$otherSubject->id}/grades", $this->gradeData())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('grades', ['subject_id' => $otherSubject->id, 'teacher_id' => $admin->id]);
    }

    public function test_teacher_cannot_grade_outside_assignments(): void
    {
        $other = $this->makeUser('teacher');
        $grade = $this->makeGrade($this->student, $this->subject, $this->teacher);

        $this->actingAs($other);
        $this->get("/gradebook/{$this->class->id}/{$this->subject->id}")->assertForbidden();
        $this->get($this->gradesUrl('grades/create'))->assertForbidden();
        $this->get($this->gradesUrl('bulk'))->assertForbidden();
        $this->post($this->gradesUrl(), $this->gradeData())->assertForbidden();
        $this->post($this->gradesUrl('bulk'), [])->assertForbidden();
        $this->get("/grades/{$grade->id}/edit")->assertForbidden();
        $this->put("/grades/{$grade->id}", $this->gradeData())->assertForbidden();
        $this->delete("/grades/{$grade->id}")->assertForbidden();

        $this->assertDatabaseCount('grades', 1);
    }

    public function test_student_from_another_class_is_rejected(): void
    {
        $outsider = $this->makeUser('student', ['class_id' => $this->makeClass('2B')->id]);

        $this->actingAs($this->teacher)
            ->post($this->gradesUrl(), $this->gradeData(['student_id' => $outsider->id]))
            ->assertSessionHasErrors('student_id');
    }

    public function test_grade_range_is_validated(): void
    {
        $this->actingAs($this->teacher);

        $this->post($this->gradesUrl(), $this->gradeData(['value' => 7]))->assertSessionHasErrors('value');
        $this->post($this->gradesUrl(), $this->gradeData(['value' => 0]))->assertSessionHasErrors('value');
        $this->post($this->gradesUrl(), $this->gradeData(['weight' => 6]))->assertSessionHasErrors('weight');
        $this->post($this->gradesUrl(), $this->gradeData(['category' => 'Inna']))->assertSessionHasErrors('category');
    }

    public function test_add_change_and_delete_are_logged_in_history(): void
    {
        $this->actingAs($this->teacher)->post($this->gradesUrl(), $this->gradeData());
        $grade = Grade::first();

        $this->put("/grades/{$grade->id}", $this->gradeData(['value' => 2]));
        $this->put("/grades/{$grade->id}", $this->gradeData(['value' => 2])); // bez zmian: brak wpisu
        $this->delete("/grades/{$grade->id}");

        $this->assertSoftDeleted($grade);
        $this->assertSame(['created', 'updated', 'deleted'], GradeHistory::orderBy('id')->pluck('action')->all());

        $updated = GradeHistory::where('action', 'updated')->first();
        $this->assertSame(5, $updated->old_value);
        $this->assertSame(2, $updated->new_value);
        $this->assertSame($this->teacher->id, $updated->changed_by);
    }

    public function test_bulk_entry_saves_only_filled_rows_and_logs_history(): void
    {
        $second = $this->makeUser('student', ['class_id' => $this->class->id]);

        $this->actingAs($this->teacher)->post($this->gradesUrl('bulk'), [
            'category' => 'Sprawdzian',
            'weight' => 5,
            'values' => [$this->student->id => 4, $second->id => ''],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('grades', 1);
        $this->assertDatabaseCount('grade_histories', 1);
    }

    public function test_bulk_entry_requires_a_grade_and_rejects_outsiders(): void
    {
        $outsider = $this->makeUser('student', ['class_id' => $this->makeClass('2B')->id]);
        $this->actingAs($this->teacher);

        $this->post($this->gradesUrl('bulk'), ['category' => 'Sprawdzian', 'weight' => 5, 'values' => [$this->student->id => '']])
            ->assertSessionHasErrors('values');

        $this->post($this->gradesUrl('bulk'), ['category' => 'Sprawdzian', 'weight' => 5, 'values' => [$outsider->id => 3]])
            ->assertSessionHasErrors('values');

        $this->assertDatabaseCount('grades', 0);
    }

    public function test_weighted_average(): void
    {
        $this->assertNull(GradeAverageCalculator::weighted([]));
        $this->assertSame('brak ocen', GradeAverageCalculator::format(null));

        $this->makeGrade($this->student, $this->subject, $this->teacher, 6, 3);
        $removed = $this->makeGrade($this->student, $this->subject, $this->teacher, 1, 5);
        $this->makeGrade($this->student, $this->subject, $this->teacher, 2, 1);

        // (6×3 + 1×5 + 2×1) / (3 + 5 + 1) = 25 / 9
        $this->assertSame(2.78, GradeAverageCalculator::weighted($this->student->grades()->get()));
        $this->assertSame(2.78, GradeAverageCalculator::classAverages()[$this->class->id]);
        $this->assertSame(2.78, GradeAverageCalculator::classSubjectAverages()[$this->class->id.'-'.$this->subject->id]);

        $removed->delete();

        $this->assertSame(5.0, GradeAverageCalculator::weighted($this->student->grades()->get()));
        $this->assertSame('5,00', GradeAverageCalculator::format(5.0));
        $this->assertSame(5.0, GradeAverageCalculator::classAverages()[$this->class->id]);
    }

    public function test_grade_sheet_shows_students_and_averages(): void
    {
        $this->makeGrade($this->student, $this->subject, $this->teacher, 5, 2);

        $this->actingAs($this->teacher)
            ->get("/gradebook/{$this->class->id}/{$this->subject->id}")
            ->assertOk()
            ->assertSee($this->student->last_name)
            ->assertSee('5,00');

        foreach (['/teacher/dashboard', '/gradebook', '/history', '/history?action=created&subject='.$this->subject->id, '/notes', '/notes/create', $this->gradesUrl('grades/create'), $this->gradesUrl('bulk')] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_teacher_history_is_limited_to_own_scope(): void
    {
        $otherTeacher = $this->makeUser('teacher');
        $otherSubject = $this->makeSubject('Geografia');
        $this->assignTeacher($this->class, $otherSubject, $otherTeacher);

        $this->makeGrade($this->student, $this->subject, $this->teacher);
        $this->makeGrade($this->student, $otherSubject, $otherTeacher);

        $this->actingAs($this->teacher)->get('/history')->assertSee('Matematyka')->assertDontSee('Geografia');
    }

    public function test_notes_rules(): void
    {
        $outsider = $this->makeUser('student', ['class_id' => $this->makeClass('2B')->id]);
        $data = ['student_id' => $this->student->id, 'type' => 'praise', 'content' => 'Brawo'];

        $this->actingAs($this->teacher)->post('/notes', $data)->assertSessionHasNoErrors();
        $this->post('/notes', ['student_id' => $outsider->id] + $data)->assertSessionHasErrors('student_id');

        $note = Note::first();
        $this->put("/notes/{$note->id}", ['type' => 'warning', 'content' => 'Zmiana'])->assertSessionHasNoErrors();
        $this->assertSame('warning', $note->fresh()->type);

        $other = $this->makeUser('teacher');
        $this->actingAs($other);
        $this->get("/notes/{$note->id}/edit")->assertForbidden();
        $this->put("/notes/{$note->id}", ['type' => 'praise', 'content' => 'X'])->assertForbidden();
        $this->delete("/notes/{$note->id}")->assertForbidden();

        $admin = $this->makeUser('admin');
        $this->actingAs($admin)->get('/notes')->assertOk()->assertSee('Zmiana');
        $this->get('/notes/create')->assertForbidden();
        $this->post('/notes', $data)->assertForbidden();
        $this->delete("/notes/{$note->id}")->assertSessionHasNoErrors();
        $this->assertDatabaseCount('notes', 0);
    }
}
