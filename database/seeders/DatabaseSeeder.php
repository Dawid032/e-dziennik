<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Note;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Dane demonstracyjne. Bez Fakera, bo w ZIP-ie nie ma pakietów deweloperskich.
 * Stałe ziarno losowania daje za każdym razem te same dane.
 */
class DatabaseSeeder extends Seeder
{
    private const MALE_NAMES = [
        'Adam', 'Bartosz', 'Damian', 'Filip', 'Grzegorz', 'Hubert', 'Igor', 'Jakub', 'Kacper', 'Łukasz',
        'Marcin', 'Mateusz', 'Michał', 'Oskar', 'Patryk', 'Piotr', 'Szymon', 'Tomasz', 'Wojciech', 'Zbigniew',
    ];

    private const FEMALE_NAMES = [
        'Agnieszka', 'Alicja', 'Barbara', 'Dominika', 'Emilia', 'Gabriela', 'Hanna', 'Iwona', 'Joanna', 'Julia',
        'Karolina', 'Katarzyna', 'Magdalena', 'Maja', 'Natalia', 'Oliwia', 'Paulina', 'Weronika', 'Zofia', 'Zuzanna',
    ];

    private const SURNAMES = [
        ['Kowalski', 'Kowalska'], ['Nowak', 'Nowak'], ['Wiśniewski', 'Wiśniewska'], ['Wójcik', 'Wójcik'],
        ['Kamiński', 'Kamińska'], ['Lewandowski', 'Lewandowska'], ['Zieliński', 'Zielińska'], ['Szymański', 'Szymańska'],
        ['Woźniak', 'Woźniak'], ['Dąbrowski', 'Dąbrowska'], ['Kozłowski', 'Kozłowska'], ['Jankowski', 'Jankowska'],
        ['Mazur', 'Mazur'], ['Krawczyk', 'Krawczyk'], ['Piotrowski', 'Piotrowska'], ['Grabowski', 'Grabowska'],
        ['Pawłowski', 'Pawłowska'], ['Michalski', 'Michalska'], ['Król', 'Król'], ['Wieczorek', 'Wieczorek'],
        ['Jabłoński', 'Jabłońska'], ['Wróbel', 'Wróbel'], ['Majewski', 'Majewska'], ['Olszewski', 'Olszewska'],
        ['Stępień', 'Stępień'], ['Malinowski', 'Malinowska'], ['Jaworski', 'Jaworska'], ['Adamczyk', 'Adamczyk'],
        ['Dudek', 'Dudek'], ['Nowicki', 'Nowicka'],
    ];

    private const TOPICS = [
        'Matematyka' => ['Funkcje liniowe', 'Równania kwadratowe', 'Trygonometria', 'Geometria analityczna'],
        'Język polski' => ['Lektura', 'Rozprawka', 'Interpunkcja', 'Epoka romantyzmu'],
        'Język angielski' => ['Reading', 'Słownictwo', 'Czasy przeszłe', 'Listening'],
        'Informatyka' => ['Algorytmy', 'Bazy danych', 'HTML i CSS', 'Podstawy PHP'],
        'Historia' => ['Średniowiecze', 'Rewolucja francuska', 'II Rzeczpospolita', 'Praca z mapą'],
        'Sieci komputerowe' => ['Model OSI', 'Adresacja IP', 'Routing', 'Konfiguracja przełącznika'],
    ];

    /** Kategoria => najniższa i najwyższa waga. */
    private const CATEGORIES = [
        'Sprawdzian' => [4, 5],
        'Kartkówka' => [2, 3],
        'Odpowiedź' => [2, 2],
        'Praca domowa' => [1, 1],
        'Aktywność' => [1, 1],
    ];

    private const VALUES = [1, 2, 2, 3, 3, 3, 3, 4, 4, 4, 4, 4, 4, 5, 5, 5, 5, 6, 6];

    private const PRAISES = [
        'Wzorowo przygotowana prezentacja na lekcji.',
        'Bezinteresowna pomoc kolegom w nauce.',
        'Reprezentowanie szkoły w konkursie przedmiotowym.',
        'Duże zaangażowanie w przygotowanie dnia otwartego.',
    ];

    private const WARNINGS = [
        'Rozmowy podczas lekcji mimo zwróconej uwagi.',
        'Brak pracy domowej po raz trzeci w miesiącu.',
        'Korzystanie z telefonu w czasie sprawdzianu.',
        'Spóźnienie na lekcję bez usprawiedliwienia.',
    ];

    private string $passwordHash;

    private int $personIndex = 0;

    private array $usedEmails = [];

    public function run(): void
    {
        mt_srand(2026);
        $this->passwordHash = Hash::make('haslo123');

        DB::transaction(function () {
            Carbon::setTestNow('2026-09-01 08:00:00');

            $teachers = $this->seedStaff();
            $subjects = $this->seedSubjects();
            $classes = $this->seedClasses($teachers);
            $assignments = $this->seedAssignments($classes, $subjects, $teachers);
            $students = $this->seedStudents($classes);
            $this->seedParents($students);
            $this->seedGrades($assignments, $students);
            $this->seedGradeChanges($students);
            $this->seedNotes($assignments, $students);
        });

        Carbon::setTestNow();
    }

    /** @return list<User> nauczyciele; pierwszy to konto nauczyciel@wsb.pl */
    private function seedStaff(): array
    {
        $this->account(User::ROLE_ADMIN, 'Marek', 'Zieliński', 'admin@wsb.pl');
        $this->account(User::ROLE_ADMIN, 'Renata', 'Mazur', 'admin2@wsb.pl');

        $teachers = [$this->account(User::ROLE_TEACHER, 'Katarzyna', 'Lewandowska', 'nauczyciel@wsb.pl')];

        for ($i = 0; $i < 5; $i++) {
            $teachers[] = $this->person(User::ROLE_TEACHER);
        }

        return $teachers;
    }

    /** @return array<string, Subject> */
    private function seedSubjects(): array
    {
        $subjects = [];

        foreach (array_keys(self::TOPICS) as $name) {
            $subjects[$name] = Subject::create(['name' => $name]);
        }

        return $subjects;
    }

    /** @return array<string, SchoolClass> */
    private function seedClasses(array $teachers): array
    {
        $homeroom = ['1A' => $teachers[0], '2B' => $teachers[1], '3Ti' => $teachers[3]];
        $classes = [];

        foreach ($homeroom as $name => $teacher) {
            $classes[$name] = SchoolClass::create([
                'name' => $name,
                'school_year' => '2026/2027',
                'homeroom_teacher_id' => $teacher->id,
            ]);
        }

        return $classes;
    }

    private function seedAssignments(array $classes, array $subjects, array $teachers): Collection
    {
        // Przedmiot => nauczyciel domyślny; informatyka w 3Ti ma osobnego nauczyciela.
        $bySubject = [
            'Matematyka' => 0,
            'Język polski' => 1,
            'Język angielski' => 2,
            'Informatyka' => 0,
            'Historia' => 4,
            'Sieci komputerowe' => 5,
        ];

        $assignments = collect();

        foreach ($classes as $className => $class) {
            foreach ($bySubject as $subjectName => $teacherIndex) {
                if ($subjectName === 'Informatyka' && $className === '3Ti') {
                    $teacherIndex = 3;
                }

                $assignments->push(TeachingAssignment::create([
                    'class_id' => $class->id,
                    'subject_id' => $subjects[$subjectName]->id,
                    'teacher_id' => $teachers[$teacherIndex]->id,
                ]));
            }
        }

        return $assignments;
    }

    /** @return array<string, list<User>> nazwa klasy => uczniowie */
    private function seedStudents(array $classes): array
    {
        $students = [
            '1A' => [$this->account(User::ROLE_STUDENT, 'Jan', 'Kowalski', 'uczen@wsb.pl', $classes['1A']->id)],
            '2B' => [$this->account(User::ROLE_STUDENT, 'Zofia', 'Kowalska', null, $classes['2B']->id)],
            '3Ti' => [],
        ];

        foreach (['1A' => 9, '2B' => 9, '3Ti' => 9] as $name => $count) {
            for ($i = 0; $i < $count; $i++) {
                $students[$name][] = $this->person(User::ROLE_STUDENT, $classes[$name]->id);
            }
        }

        return $students;
    }

    private function seedParents(array $students): void
    {
        $parent = $this->account(User::ROLE_PARENT, 'Ewa', 'Kowalska', 'rodzic@wsb.pl');
        $parent->children()->attach([$students['1A'][0]->id, $students['2B'][0]->id]);

        // Dzieci kolejnych rodziców, wybierane po kolei z różnych klas.
        $children = [
            $students['1A'][1], $students['1A'][2], $students['2B'][1], $students['2B'][2], $students['3Ti'][0],
            $students['3Ti'][1], $students['3Ti'][2], $students['1A'][3], $students['1A'][3],
        ];

        foreach ($children as $i => $child) {
            $gender = $i % 2;
            $names = $gender ? self::FEMALE_NAMES : self::MALE_NAMES;
            $firstName = $names[($i * 3 + 5) % count($names)];

            $this->account(User::ROLE_PARENT, $firstName, $this->surnameForGender($child->last_name, $gender))
                ->children()->attach($child->id);
        }
    }

    private function seedGrades(Collection $assignments, array $students): void
    {
        $classStudents = collect($students)->flatten()->groupBy('class_id');
        $subjectNames = Subject::pluck('name', 'id');

        foreach ($assignments as $assignment) {
            $topics = self::TOPICS[$subjectNames[$assignment->subject_id]];

            foreach ($classStudents[$assignment->class_id] as $student) {
                $days = [];

                for ($n = mt_rand(4, 8); $n > 0; $n--) {
                    $days[] = mt_rand(0, 50);
                }

                sort($days);

                foreach ($days as $day) {
                    $category = array_rand(self::CATEGORIES);
                    [$minWeight, $maxWeight] = self::CATEGORIES[$category];

                    Carbon::setTestNow($this->schoolDay($day));

                    Grade::create([
                        'student_id' => $student->id,
                        'subject_id' => $assignment->subject_id,
                        'teacher_id' => $assignment->teacher_id,
                        'value' => self::VALUES[mt_rand(0, count(self::VALUES) - 1)],
                        'weight' => mt_rand($minWeight, $maxWeight),
                        'category' => $category,
                        'description' => mt_rand(0, 2) > 0 ? $topics[mt_rand(0, count($topics) - 1)] : null,
                    ]);
                }
            }
        }
    }

    /** Zmiany i usunięcia wykonane przez model, żeby historia nie była pusta. */
    private function seedGradeChanges(array $students): void
    {
        $demoGrades = Grade::where('student_id', $students['1A'][0]->id)->orderBy('id')->limit(3)->get();

        Carbon::setTestNow('2026-11-02 10:15:00');
        $demoGrades[0]->update(['value' => $demoGrades[0]->value === 6 ? 5 : $demoGrades[0]->value + 1]);

        Carbon::setTestNow('2026-11-02 10:20:00');
        $demoGrades[1]->update(['category' => 'Sprawdzian', 'weight' => 5]);

        Carbon::setTestNow('2026-11-03 12:40:00');
        $demoGrades[2]->delete();

        $minutes = 0;

        foreach (Grade::orderBy('id')->get() as $grade) {
            $roll = mt_rand(1, 100);

            if ($roll > 8) {
                continue;
            }

            Carbon::setTestNow(Carbon::parse('2026-11-04 08:00:00')->addMinutes($minutes += 7));

            if ($roll <= 6) {
                $grade->update(['value' => $grade->value === 6 ? 5 : $grade->value + 1]);
            } else {
                $grade->delete();
            }
        }
    }

    private function seedNotes(Collection $assignments, array $students): void
    {
        $allStudents = collect($students)->flatten()->values();
        $demoStudent = $students['1A'][0];
        $demoTeacher = User::where('email', 'nauczyciel@wsb.pl')->first();

        Carbon::setTestNow('2026-10-05 11:30:00');
        Note::create(['student_id' => $demoStudent->id, 'teacher_id' => $demoTeacher->id, 'type' => Note::TYPE_PRAISE, 'content' => self::PRAISES[0]]);

        Carbon::setTestNow('2026-10-19 09:10:00');
        Note::create(['student_id' => $demoStudent->id, 'teacher_id' => $demoTeacher->id, 'type' => Note::TYPE_WARNING, 'content' => self::WARNINGS[0]]);

        for ($i = 0; $i < 13; $i++) {
            $student = $allStudents[($i * 5 + 2) % $allStudents->count()];
            $isPraise = $i % 2 === 0;
            $contents = $isPraise ? self::PRAISES : self::WARNINGS;

            Carbon::setTestNow($this->schoolDay(($i * 4) % 50));

            Note::create([
                'student_id' => $student->id,
                'teacher_id' => $assignments->firstWhere('class_id', $student->class_id)->teacher_id,
                'type' => $isPraise ? Note::TYPE_PRAISE : Note::TYPE_WARNING,
                'content' => $contents[$i % count($contents)],
            ]);
        }
    }

    private function person(string $role, ?int $classId = null): User
    {
        $i = $this->personIndex++;
        $gender = $i % 2;
        $names = $gender ? self::FEMALE_NAMES : self::MALE_NAMES;

        return $this->account(
            $role,
            $names[($i * 7) % count($names)],
            self::SURNAMES[($i * 11 + 3) % count(self::SURNAMES)][$gender],
            null,
            $classId,
        );
    }

    private function account(string $role, string $firstName, string $lastName, ?string $email = null, ?int $classId = null): User
    {
        return User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email ?? $this->uniqueEmail($firstName, $lastName),
            'password' => $this->passwordHash,
            'role' => $role,
            'class_id' => $classId,
        ]);
    }

    private function uniqueEmail(string $firstName, string $lastName): string
    {
        $base = Str::lower(Str::ascii($firstName.'.'.$lastName));
        $candidate = $base;

        for ($n = 2; isset($this->usedEmails[$candidate]); $n++) {
            $candidate = $base.$n;
        }

        $this->usedEmails[$candidate] = true;

        return $candidate.'@wsb.pl';
    }

    private function surnameForGender(string $lastName, int $gender): string
    {
        foreach (self::SURNAMES as $pair) {
            if (in_array($lastName, $pair, true)) {
                return $pair[$gender];
            }
        }

        return $lastName;
    }

    /** Dzień szkolny liczony od początku września, z pominięciem weekendów. */
    private function schoolDay(int $offset): Carbon
    {
        $date = Carbon::parse('2026-09-07')->addDays($offset)->setTime(mt_rand(8, 14), mt_rand(0, 59));

        return $date->isWeekend() ? $date->addDays(2) : $date;
    }
}
