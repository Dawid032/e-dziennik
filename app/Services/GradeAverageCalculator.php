<?php

namespace App\Services;

use App\Models\Grade;
use Illuminate\Support\Collection;

class GradeAverageCalculator
{
    /**
     * Średnia ważona: suma (ocena × waga) podzielona przez sumę wag.
     * Zwraca null, gdy nie ma żadnej oceny.
     */
    public static function weighted(iterable $grades): ?float
    {
        $weightedSum = 0;
        $weightSum = 0;

        foreach ($grades as $grade) {
            $weightedSum += $grade->value * $grade->weight;
            $weightSum += $grade->weight;
        }

        return $weightSum > 0 ? round($weightedSum / $weightSum, 2) : null;
    }

    /** Tekst do wyświetlenia w widoku. */
    public static function format(?float $average): string
    {
        return $average === null ? 'brak ocen' : number_format($average, 2, ',', '');
    }

    /** Średnia ważona każdej klasy: [class_id => średnia]. */
    public static function classAverages(): Collection
    {
        return Grade::query()
            ->join('users', 'users.id', '=', 'grades.student_id')
            ->selectRaw('users.class_id as class_id, SUM(grades.value * grades.weight) * 1.0 / SUM(grades.weight) as average')
            ->groupBy('users.class_id')
            ->pluck('average', 'class_id')
            ->map(fn ($average) => round((float) $average, 2));
    }

    /** Średnia ważona par klasa–przedmiot: ["class_id-subject_id" => średnia]. */
    public static function classSubjectAverages(): Collection
    {
        return Grade::query()
            ->join('users', 'users.id', '=', 'grades.student_id')
            ->selectRaw('users.class_id as class_id, grades.subject_id as subject_id, SUM(grades.value * grades.weight) * 1.0 / SUM(grades.weight) as average')
            ->groupBy('users.class_id', 'grades.subject_id')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->class_id.'-'.$row->subject_id => round((float) $row->average, 2)]);
    }
}
