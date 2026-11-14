<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->makeUser('admin');
        $this->actingAs($this->admin);
    }

    private function validUser(array $overrides = []): array
    {
        return $overrides + [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan.kowalski@wsb.pl',
            'role' => 'teacher',
            'password' => 'haslo123',
            'password_confirmation' => 'haslo123',
        ];
    }

    public function test_admin_creates_user_with_hashed_password(): void
    {
        $this->post('/admin/users', $this->validUser())->assertSessionHasNoErrors();

        $user = User::where('email', 'jan.kowalski@wsb.pl')->first();
        $this->assertNotSame('haslo123', $user->password);
        $this->assertTrue(Hash::check('haslo123', $user->password));
    }

    public function test_admin_can_create_another_admin(): void
    {
        $this->post('/admin/users', $this->validUser(['role' => 'admin']))->assertSessionHasNoErrors();

        $this->assertSame('admin', User::where('email', 'jan.kowalski@wsb.pl')->value('role'));
    }

    public function test_student_requires_a_class_and_parent_requires_children(): void
    {
        $this->post('/admin/users', $this->validUser(['role' => 'student']))->assertSessionHasErrors('class_id');
        $this->post('/admin/users', $this->validUser(['role' => 'parent']))->assertSessionHasErrors('children');
    }

    public function test_child_must_be_a_student(): void
    {
        $teacher = $this->makeUser('teacher');

        $this->post('/admin/users', $this->validUser(['role' => 'parent', 'children' => [$teacher->id]]))
            ->assertSessionHasErrors('children.0');
    }

    public function test_list_and_single_user_are_visible(): void
    {
        $user = $this->makeUser('teacher', ['first_name' => 'Barbara', 'last_name' => 'Wrona']);

        $this->get('/admin/users')->assertOk()->assertSee('Wrona Barbara');
        $this->get("/admin/users/{$user->id}")->assertOk()->assertSee('Barbara Wrona');
    }

    public function test_update_keeps_password_when_left_empty(): void
    {
        $user = $this->makeUser('teacher');
        $before = $user->password;

        $this->put("/admin/users/{$user->id}", $this->validUser(['password' => '', 'password_confirmation' => '', 'last_name' => 'Zmieniony']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Zmieniony', $user->fresh()->last_name);
        $this->assertSame($before, $user->fresh()->password);
    }

    public function test_changing_role_clears_class_and_children(): void
    {
        $class = $this->makeClass();
        $student = $this->makeUser('student', ['class_id' => $class->id]);

        $this->put("/admin/users/{$student->id}", $this->validUser(['role' => 'parent', 'children' => [$this->makeUser('student', ['class_id' => $class->id])->id]]))
            ->assertSessionHasNoErrors();

        $this->assertNull($student->fresh()->class_id);
        $this->assertCount(1, $student->fresh()->children);

        $this->put("/admin/users/{$student->id}", $this->validUser(['role' => 'teacher']))->assertSessionHasNoErrors();
        $this->assertCount(0, $student->fresh()->children);
    }

    public function test_admin_cannot_delete_or_demote_self(): void
    {
        $this->delete("/admin/users/{$this->admin->id}")->assertSessionHas('error');
        $this->assertNotNull($this->admin->fresh());

        $this->from("/admin/users/{$this->admin->id}/edit")
            ->put("/admin/users/{$this->admin->id}", $this->validUser(['email' => $this->admin->email, 'role' => 'teacher']))
            ->assertSessionHasErrors('role');

        $this->assertSame('admin', $this->admin->fresh()->role);
    }

    public function test_teacher_with_records_cannot_be_deleted_or_change_role(): void
    {
        $teacher = $this->makeUser('teacher');
        $class = $this->makeClass();
        $subject = $this->makeSubject();
        $student = $this->makeUser('student', ['class_id' => $class->id]);
        $this->makeGrade($student, $subject, $teacher)->delete();

        $this->delete("/admin/users/{$teacher->id}")->assertSessionHas('error');
        $this->assertNotNull($teacher->fresh());

        $this->from("/admin/users/{$teacher->id}/edit")
            ->put("/admin/users/{$teacher->id}", $this->validUser(['email' => $teacher->email, 'role' => 'student', 'class_id' => $class->id]))
            ->assertSessionHasErrors('role');
    }

    public function test_deleting_a_student_removes_grades_history_and_parent_links(): void
    {
        $teacher = $this->makeUser('teacher');
        $class = $this->makeClass();
        $student = $this->makeUser('student', ['class_id' => $class->id]);
        $parent = $this->makeUser('parent');
        $parent->children()->attach($student->id);
        $this->makeGrade($student, $this->makeSubject(), $teacher);

        $this->delete("/admin/users/{$student->id}")->assertSessionHasNoErrors()->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
        $this->assertDatabaseCount('grades', 0);
        $this->assertDatabaseCount('grade_histories', 0);
        $this->assertDatabaseCount('parent_student', 0);
    }

    public function test_class_with_students_and_subject_with_grades_cannot_be_deleted(): void
    {
        $teacher = $this->makeUser('teacher');
        $class = $this->makeClass();
        $subject = $this->makeSubject();
        $student = $this->makeUser('student', ['class_id' => $class->id]);
        $this->makeGrade($student, $subject, $teacher)->delete();

        $this->delete("/admin/classes/{$class->id}")->assertSessionHas('error');
        $this->delete("/admin/subjects/{$subject->id}")->assertSessionHas('error');
        $this->assertNotNull(SchoolClass::find($class->id));
        $this->assertNotNull(Subject::find($subject->id));
    }

    public function test_class_subject_and_assignment_crud(): void
    {
        $teacher = $this->makeUser('teacher');

        $this->post('/admin/classes', ['name' => '2B', 'school_year' => '2026/2027', 'homeroom_teacher_id' => $teacher->id])->assertSessionHasNoErrors();
        $this->post('/admin/subjects', ['name' => 'Sieci komputerowe'])->assertSessionHasNoErrors();

        $class = SchoolClass::where('name', '2B')->first();
        $subject = Subject::where('name', 'Sieci komputerowe')->first();

        $data = ['class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id];
        $this->post('/admin/assignments', $data)->assertSessionHasNoErrors();
        $this->post('/admin/assignments', $data)->assertSessionHasErrors('subject_id');

        $this->post('/admin/classes', ['name' => '2B', 'school_year' => '2026/2027'])->assertSessionHasErrors('name');

        foreach (['/admin/classes', '/admin/classes/create', "/admin/classes/{$class->id}/edit", '/admin/subjects', '/admin/subjects/create',
            "/admin/subjects/{$subject->id}/edit", '/admin/assignments', '/admin/assignments/create', '/admin/users/create', '/admin/dashboard'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_homeroom_teacher_must_have_teacher_role(): void
    {
        $student = $this->makeUser('student');

        $this->post('/admin/classes', ['name' => '2B', 'school_year' => '2026/2027', 'homeroom_teacher_id' => $student->id])
            ->assertSessionHasErrors('homeroom_teacher_id');
    }

    public function test_search_filters_and_pagination_keeps_parameters(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->makeUser('teacher', ['last_name' => 'Szukany', 'first_name' => 'Marek']);
        }

        $this->makeUser('student', ['last_name' => 'Inny', 'first_name' => 'Tomasz']);

        $response = $this->get('/admin/users?q=Szukany&role=teacher');

        $response->assertOk()->assertSee('Szukany')->assertDontSee('Inny Tomasz');
        $response->assertSee('q=Szukany&amp;role=teacher&amp;page=2', false);

        $this->get('/admin/users?q=Marek+Szukany&page=2')->assertOk()->assertSee('Szukany');
        $this->get('/admin/users?q[]=x&role[]=y')->assertOk();
    }
}
