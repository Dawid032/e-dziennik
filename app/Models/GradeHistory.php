<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeHistory extends Model
{
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';

    public const ACTIONS = [
        self::ACTION_CREATED => 'Dodano',
        self::ACTION_UPDATED => 'Zmieniono',
        self::ACTION_DELETED => 'Usunięto',
    ];

    // Wpis historii nigdy się nie zmienia, więc nie ma kolumny updated_at.
    public const UPDATED_AT = null;

    protected $table = 'grade_histories';

    protected $fillable = [
        'grade_id',
        'action',
        'old_value',
        'new_value',
        'old_weight',
        'new_weight',
        'old_category',
        'new_category',
        'old_description',
        'new_description',
        'changed_by',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class)->withTrashed();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /* Zakresy zapytań */

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->whereHas('grade', fn (Builder $g) => $g->where('student_id', $studentId));
    }

    /** Zmiany widoczne dla nauczyciela: jego przydziały i klasy, w których jest wychowawcą. */
    public function scopeVisibleToTeacher(Builder $query, User $teacher): Builder
    {
        $assignments = $teacher->teachingAssignments()->get(['class_id', 'subject_id']);
        $homeroomClassIds = $teacher->homeroomClasses()->pluck('id');

        if ($assignments->isEmpty() && $homeroomClassIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('grade', function (Builder $grade) use ($assignments, $homeroomClassIds) {
            $grade->where(function (Builder $scope) use ($assignments, $homeroomClassIds) {
                foreach ($assignments as $assignment) {
                    $scope->orWhere(function (Builder $pair) use ($assignment) {
                        $pair->where('subject_id', $assignment->subject_id)
                            ->whereHas('student', fn (Builder $s) => $s->where('class_id', $assignment->class_id));
                    });
                }

                if ($homeroomClassIds->isNotEmpty()) {
                    $scope->orWhereHas('student', fn (Builder $s) => $s->whereIn('class_id', $homeroomClassIds));
                }
            });
        });
    }

    /* Atrybuty do widoków */

    public function getActionNameAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    public function getBeforeAttribute(): string
    {
        return $this->describe($this->old_value, $this->old_weight, $this->old_category);
    }

    public function getAfterAttribute(): string
    {
        return $this->describe($this->new_value, $this->new_weight, $this->new_category);
    }

    private function describe(?int $value, ?int $weight, ?string $category): string
    {
        return $value === null ? '—' : "{$value} (waga {$weight}, {$category})";
    }
}
