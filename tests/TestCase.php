<?php

namespace Tests;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private int $sequence = 0;

    protected function makeUser(string $role, array $attributes = []): User
    {
        $this->sequence++;

        return User::create($attributes + [
            'first_name' => 'Anna',
            'last_name' => 'Nowak',
            'email' => "user{$this->sequence}@wsb.pl",
            'password' => 'haslo123',
            'role' => $role,
        ]);
    }

    protected function makeClass(string $name = '1A', ?User $homeroom = null): SchoolClass
    {
        return SchoolClass::create([
            'name' => $name,
            'school_year' => '2026/2027',
            'homeroom_teacher_id' => $homeroom?->id,
        ]);
    }

    protected function makeSubject(string $name = 'Matematyka'): Subject
    {
        return Subject::create(['name' => $name]);
    }

    protected function assignTeacher(SchoolClass $class, Subject $subject, User $teacher): TeachingAssignment
    {
        return TeachingAssignment::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    protected function makeGrade(User $student, Subject $subject, User $teacher, int $value = 4, int $weight = 1): Grade
    {
        return Grade::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'value' => $value,
            'weight' => $weight,
            'category' => 'Sprawdzian',
        ]);
    }
}
