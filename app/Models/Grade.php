<?php

namespace App\Models;

use App\Observers\GradeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(GradeObserver::class)]
class Grade extends Model
{
    // Usunięta ocena zostaje w bazie (deleted_at), dzięki czemu historia przetrwa.
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'subject_id',
        'teacher_id',
        'value',
        'weight',
        'category',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'weight' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(GradeHistory::class);
    }

    /** Tekst dymka nad oceną w widokach. */
    public function getTooltipAttribute(): string
    {
        return trim($this->category.', waga '.$this->weight.($this->description ? ': '.$this->description : ''));
    }
}
