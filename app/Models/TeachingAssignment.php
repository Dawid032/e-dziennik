<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingAssignment extends Model
{
    protected $table = 'class_subject_teacher';

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy(SchoolClass::select('name')->whereColumn('school_classes.id', 'class_subject_teacher.class_id'))
            ->orderBy(Subject::select('name')->whereColumn('subjects.id', 'class_subject_teacher.subject_id'));
    }

    public function scopeSearch(Builder $query, mixed $term): Builder
    {
        $term = is_string($term) ? trim($term) : '';

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->whereHas('schoolClass', fn (Builder $c) => $c->where('name', 'like', "%{$term}%"))
                ->orWhereHas('subject', fn (Builder $s) => $s->where('name', 'like', "%{$term}%"))
                ->orWhereHas('teacher', fn (Builder $t) => $t->search($term));
        });
    }
}
