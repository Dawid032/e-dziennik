<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    public const TYPE_PRAISE = 'praise';
    public const TYPE_WARNING = 'warning';

    public const TYPES = [
        self::TYPE_PRAISE => 'Pochwała',
        self::TYPE_WARNING => 'Uwaga',
    ];

    protected $fillable = [
        'student_id',
        'teacher_id',
        'type',
        'content',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
