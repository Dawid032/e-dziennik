<?php

namespace App\Models;

use App\Services\GradeAverageCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_STUDENT = 'student';
    public const ROLE_PARENT = 'parent';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_TEACHER => 'Nauczyciel',
        self::ROLE_STUDENT => 'Uczeń',
        self::ROLE_PARENT => 'Rodzic',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'class_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /* Relacje */

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'student_id');
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'teacher_id');
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    /* Zakresy zapytań */

    public function scopeOfRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    public function scopeAlphabetical(Builder $query): Builder
    {
        return $query->orderBy('last_name')->orderBy('first_name');
    }

    /** Każde słowo musi pasować do imienia, nazwiska lub e-maila. */
    public function scopeSearch(Builder $query, mixed $term): Builder
    {
        $term = is_string($term) ? trim($term) : '';

        foreach (preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY) as $word) {
            $query->where(function (Builder $q) use ($word) {
                $q->where('first_name', 'like', "%{$word}%")
                    ->orWhere('last_name', 'like', "%{$word}%")
                    ->orWhere('email', 'like', "%{$word}%");
            });
        }

        return $query;
    }

    /* Atrybuty do widoków */

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getRoleNameAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    /* Role */

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function isParent(): bool
    {
        return $this->role === self::ROLE_PARENT;
    }

    /** Nazwa trasy panelu, na który użytkownik trafia po zalogowaniu. */
    public function dashboardRoute(): string
    {
        return $this->role.'.dashboard';
    }

    /* Uprawnienia do konkretnych zasobów */

    public function canGrade(?int $classId, int $subjectId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isTeacher()
            && $this->teachingAssignments()->where('class_id', $classId)->where('subject_id', $subjectId)->exists();
    }

    public function isHomeroomTeacherOf(SchoolClass $class): bool
    {
        return $this->isTeacher() && $class->homeroom_teacher_id === $this->id;
    }

    /** Identyfikatory klas, których uczy nauczyciel lub w których jest wychowawcą. */
    public function relatedClassIds(): Collection
    {
        return $this->teachingAssignments()->pluck('class_id')
            ->merge($this->homeroomClasses()->pluck('id'))
            ->unique()
            ->values();
    }

    public function canWriteNoteFor(User $student): bool
    {
        return $this->isTeacher()
            && $student->isStudent()
            && $this->relatedClassIds()->contains($student->class_id);
    }

    /* Blokady usuwania i zmiany roli */

    /** Powód, dla którego konta nie można usunąć, albo null, gdy można. */
    public function deletionBlockReason(): ?string
    {
        // withTrashed, bo klucz obcy widzi również oceny usunięte miękko.
        if (Grade::withTrashed()->where('teacher_id', $this->id)->exists()) {
            return 'To konto jest autorem ocen.';
        }

        if ($this->teachingAssignments()->exists()) {
            return 'To konto ma przydziały nauczania.';
        }

        if (GradeHistory::where('changed_by', $this->id)->exists()) {
            return 'To konto występuje w historii zmian ocen.';
        }

        if (Note::where('teacher_id', $this->id)->exists()) {
            return 'To konto jest autorem uwag.';
        }

        return null;
    }

    /** Powód, dla którego nie można zmienić roli konta, albo null, gdy można. */
    public function roleChangeBlockReason(): ?string
    {
        if ($reason = $this->deletionBlockReason()) {
            return $reason;
        }

        if (Grade::withTrashed()->where('student_id', $this->id)->exists()) {
            return 'To konto ma wpisane oceny jako uczeń.';
        }

        if ($this->homeroomClasses()->exists()) {
            return 'To konto jest wychowawcą klasy.';
        }

        return null;
    }

    /* Dane ucznia */

    /** Oceny pogrupowane według przedmiotów, ze średnią ważoną każdego przedmiotu. */
    public function gradesBySubject(): Collection
    {
        return $this->grades()->with(['subject', 'teacher'])->oldest()->get()
            ->groupBy('subject_id')
            ->map(fn ($grades) => (object) [
                'subject' => $grades->first()->subject,
                'grades' => $grades,
                'average' => GradeAverageCalculator::weighted($grades),
            ])
            ->sortBy(fn ($row) => $row->subject->name)
            ->values();
    }

    public function overallAverage(): ?float
    {
        return GradeAverageCalculator::weighted($this->grades);
    }
}
