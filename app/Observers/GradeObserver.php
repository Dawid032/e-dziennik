<?php

namespace App\Observers;

use App\Models\Grade;
use App\Models\GradeHistory;

/**
 * Zapisuje każdą zmianę oceny w tabeli grade_histories.
 * Działa tylko dla operacji wykonanych przez model (Grade::create, update, delete).
 */
class GradeObserver
{
    public function created(Grade $grade): void
    {
        $this->log($grade, GradeHistory::ACTION_CREATED, [
            'new_value' => $grade->value,
            'new_weight' => $grade->weight,
            'new_category' => $grade->category,
            'new_description' => $grade->description,
        ]);
    }

    public function updated(Grade $grade): void
    {
        if (! $grade->wasChanged(['value', 'weight', 'category', 'description'])) {
            return;
        }

        $this->log($grade, GradeHistory::ACTION_UPDATED, [
            'old_value' => $grade->getOriginal('value'),
            'new_value' => $grade->value,
            'old_weight' => $grade->getOriginal('weight'),
            'new_weight' => $grade->weight,
            'old_category' => $grade->getOriginal('category'),
            'new_category' => $grade->category,
            'old_description' => $grade->getOriginal('description'),
            'new_description' => $grade->description,
        ]);
    }

    public function deleted(Grade $grade): void
    {
        $this->log($grade, GradeHistory::ACTION_DELETED, [
            'old_value' => $grade->value,
            'old_weight' => $grade->weight,
            'old_category' => $grade->category,
            'old_description' => $grade->description,
        ]);
    }

    private function log(Grade $grade, string $action, array $changes): void
    {
        GradeHistory::create($changes + [
            'grade_id' => $grade->id,
            'action' => $action,
            // W seederze nie ma zalogowanego użytkownika, wtedy autorem jest wystawiający ocenę.
            'changed_by' => auth()->id() ?? $grade->teacher_id,
        ]);
    }
}
