<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentParentTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private User $student;

    private User $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = $this->makeUser('teacher');
        $class = $this->makeClass('1A', $this->teacher);
        $subject = $this->makeSubject('Matematyka');
        $otherSubject = $this->makeSubject('Geografia');

        $this->student = $this->makeUser('student', ['class_id' => $class->id, 'first_name' => 'Jan']);
        $this->otherStudent = $this->makeUser('student', ['class_id' => $class->id, 'first_name' => 'Ola']);

        $this->makeGrade($this->student, $subject, $this->teacher, 5, 2);
        $this->makeGrade($this->otherStudent, $otherSubject, $this->teacher, 3, 1);

        Note::create(['student_id' => $this->student->id, 'teacher_id' => $this->teacher->id, 'type' => 'praise', 'content' => 'Pochwała Jana']);
        Note::create(['student_id' => $this->otherStudent->id, 'teacher_id' => $this->teacher->id, 'type' => 'warning', 'content' => 'Uwaga Oli']);
    }

    public function test_student_sees_only_own_data(): void
    {
        $this->actingAs($this->student);

        $this->get('/student/dashboard')->assertOk()->assertSee('Pochwała Jana')->assertDontSee('Uwaga Oli');
        $this->get('/student/grades')->assertOk()->assertSee('Matematyka')->assertSee('5,00')->assertDontSee('Geografia');
        $this->get('/student/history')->assertOk()->assertSee('Matematyka')->assertDontSee('Geografia');
        $this->get('/student/notes')->assertOk()->assertSee('Pochwała Jana')->assertDontSee('Uwaga Oli');
    }

    public function test_deleted_grade_stays_in_student_history(): void
    {
        $this->student->grades()->first()->delete();

        $this->actingAs($this->student)->get('/student/history')->assertOk()->assertSee('Usunięto');
        $this->get('/student/grades')->assertSee('Nie ma jeszcze żadnych ocen');
    }

    public function test_parent_sees_assigned_child(): void
    {
        $parent = $this->makeUser('parent');
        $parent->children()->attach($this->student->id);
        $this->actingAs($parent);

        $this->get('/parent/dashboard')->assertOk()->assertSee($this->student->full_name)->assertDontSee('Ola Nowak');
        $this->get("/parent/children/{$this->student->id}")->assertOk()->assertSee('Matematyka')->assertDontSee('Geografia');
        $this->get("/parent/children/{$this->student->id}/history")->assertOk()->assertSee('Dodano');
        $this->get("/parent/children/{$this->student->id}/notes")->assertOk()->assertSee('Pochwała Jana');
    }

    public function test_parent_gets_403_for_a_foreign_child(): void
    {
        $parent = $this->makeUser('parent');
        $parent->children()->attach($this->student->id);
        $this->actingAs($parent);

        $this->get("/parent/children/{$this->otherStudent->id}")->assertForbidden();
        $this->get("/parent/children/{$this->otherStudent->id}/history")->assertForbidden();
        $this->get("/parent/children/{$this->otherStudent->id}/notes")->assertForbidden();
    }

    public function test_homeroom_views_show_class_data_read_only(): void
    {
        $class = $this->student->schoolClass;
        $this->actingAs($this->teacher);

        $this->get("/teacher/homeroom/{$class->id}")->assertOk()->assertSee('5,00');
        $this->get("/teacher/homeroom/{$class->id}/students/{$this->student->id}")->assertOk()->assertSee('Pochwała Jana');

        $outsider = $this->makeUser('student', ['class_id' => $this->makeClass('2B')->id]);
        $this->get("/teacher/homeroom/{$class->id}/students/{$outsider->id}")->assertNotFound();
    }
}
