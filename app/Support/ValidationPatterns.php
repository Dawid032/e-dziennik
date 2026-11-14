<?php

namespace App\Support;

/**
 * Wszystkie wyrażenia regularne aplikacji.
 *
 * Te same stałe trafiają do reguły serwera i do atrybutu HTML `pattern`.
 * Przeglądarki kompilują `pattern` z flagą `v`, dlatego w klasach znaków
 * myślnik i nawiasy muszą być zapisane z ukośnikiem: \- \( \).
 */
final class ValidationPatterns
{
    /** Imię lub nazwisko: Anna, Nowak-Kowalska. */
    public const NAME = "^\\p{Lu}[\\p{L}'\\-]+(?: \\p{Lu}[\\p{L}'\\-]+)*$";

    /** Hasło: co najmniej 8 znaków, w tym litera i cyfra. */
    public const PASSWORD = '^(?=.*\\p{L})(?=.*\\d).{8,}$';

    /** Nazwa klasy: 1A, 3Ti. */
    public const CLASS_NAME = '^[1-8][A-Z][A-Za-z]{0,2}$';

    /** Rok szkolny: 2026/2027. */
    public const SCHOOL_YEAR = '^(20\\d{2})/(20\\d{2})$';

    /** Nazwa przedmiotu: Sieci komputerowe. */
    public const SUBJECT_NAME = '^\\p{L}[\\p{L}\\d .,\\(\\)\\-]{1,79}$';

    /** Zamienia wzorzec na regułę walidacji Laravela. */
    public static function rule(string $pattern): string
    {
        return 'regex:~'.$pattern.'~u';
    }
}
