<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function forbiddenPaths(): array
    {
        return [
            'nauczyciel w panelu admina' => ['teacher', '/admin/dashboard'],
            'nauczyciel w użytkownikach' => ['teacher', '/admin/users'],
            'nauczyciel w panelu ucznia' => ['teacher', '/student/dashboard'],
            'nauczyciel w panelu rodzica' => ['teacher', '/parent/dashboard'],
            'uczeń w panelu admina' => ['student', '/admin/dashboard'],
            'uczeń w panelu nauczyciela' => ['student', '/teacher/dashboard'],
            'uczeń w panelu rodzica' => ['student', '/parent/dashboard'],
            'uczeń w ocenach' => ['student', '/gradebook'],
            'uczeń w historii ogólnej' => ['student', '/history'],
            'uczeń w uwagach' => ['student', '/notes'],
            'rodzic w panelu admina' => ['parent', '/admin/dashboard'],
            'rodzic w panelu nauczyciela' => ['parent', '/teacher/dashboard'],
            'rodzic w panelu ucznia' => ['parent', '/student/dashboard'],
            'rodzic w ocenach' => ['parent', '/gradebook'],
            'admin w panelu nauczyciela' => ['admin', '/teacher/dashboard'],
            'admin w panelu ucznia' => ['admin', '/student/dashboard'],
            'admin w panelu rodzica' => ['admin', '/parent/dashboard'],
        ];
    }

    #[DataProvider('forbiddenPaths')]
    public function test_foreign_panel_returns_403(string $role, string $path): void
    {
        $this->actingAs($this->makeUser($role))->get($path)->assertForbidden();
    }

    public function test_403_page_is_in_polish(): void
    {
        $this->actingAs($this->makeUser('student'))->get('/admin/dashboard')->assertSee('Brak dostępu');
    }

    public function test_homeroom_panel_is_only_for_the_homeroom_teacher(): void
    {
        $homeroom = $this->makeUser('teacher');
        $other = $this->makeUser('teacher');
        $class = $this->makeClass('1A', $homeroom);

        $this->actingAs($homeroom)->get("/teacher/homeroom/{$class->id}")->assertOk();
        $this->actingAs($other)->get("/teacher/homeroom/{$class->id}")->assertForbidden();
    }

    public function test_teacher_sees_only_own_assignments_on_the_list(): void
    {
        $teacher = $this->makeUser('teacher');
        $other = $this->makeUser('teacher');
        $class = $this->makeClass();
        $this->assignTeacher($class, $this->makeSubject('Historia'), $teacher);
        $this->assignTeacher($class, $this->makeSubject('Geografia'), $other);

        $this->actingAs($teacher)->get('/gradebook')->assertSee('Historia')->assertDontSee('Geografia');
    }
}
