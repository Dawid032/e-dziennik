# E-Dziennik

Dziennik elektroniczny w Laravelu 12. Projekt zaliczeniowy z przedmiotu Programowanie w zastosowaniach, grupa K37_9, WSB Poznań.

## Konta testowe

Hasło każdego konta: `haslo123`

| E-mail | Rola | Co pokazuje |
|---|---|---|
| `admin@wsb.pl` | Administrator | użytkownicy, klasy, przedmioty, przydziały, oceny, statystyki |
| `nauczyciel@wsb.pl` | Nauczyciel | arkusze ocen, wpis zbiorczy, uwagi, panel wychowawcy klasy 1A |
| `uczen@wsb.pl` | Uczeń | oceny ze średnią ważoną, historia zmian, uwagi |
| `rodzic@wsb.pl` | Rodzic | dwoje dzieci, w tym `uczen@wsb.pl` |

Konta zakłada wyłącznie administrator: nie ma publicznej rejestracji.

## Wymagania

- PHP 8.2 lub nowsze (obsługiwane 8.2–8.5, zalecane najnowsze wydanie z poprawkami bezpieczeństwa),
- XAMPP z Apache i MySQL/MariaDB.

Nic więcej nie trzeba instalować. Katalog `vendor/` i gotowy plik `.env` są w archiwum.

## Uruchomienie krok po kroku

1. Wypakuj archiwum do katalogu `htdocs`, tak aby powstał folder `wsb_2026_k37_9`:
   - Windows: `C:\xampp\htdocs\wsb_2026_k37_9`,
   - macOS: `/Applications/XAMPP/xamppfiles/htdocs/wsb_2026_k37_9`.
2. W panelu XAMPP włącz **Apache** i **MySQL**.
3. Otwórz `http://localhost/phpmyadmin`, wybierz **Import** i wskaż plik `database/dump/wsb_2026_k37_9.sql`. Plik sam tworzy bazę `wsb_2026_k37_9`.
4. Otwórz `http://localhost/wsb_2026_k37_9/`.

Na macOS i Linuksie serwer musi móc zapisywać w dwóch katalogach:

```bash
chmod -R a+rwX storage bootstrap/cache
```

### Inne sposoby

Wbudowany serwer zamiast Apache (baza zaimportowana jak wyżej):

```bash
php artisan serve
```

Aplikacja działa wtedy pod adresem `http://127.0.0.1:8000`.

Odtworzenie bazy z migracji i danych demo zamiast importu. Najpierw utwórz w phpMyAdmin pustą bazę `wsb_2026_k37_9` z kodowaniem `utf8mb4_unicode_ci`, potem:

```bash
php artisan migrate:fresh --seed
```

W systemie Windows, jeśli polecenie `php` nie działa, użyj pełnej ścieżki `C:\xampp\php\php.exe`, a na macOS `/Applications/XAMPP/xamppfiles/bin/php`.

### Uruchomienie z repozytorium GitHub

Repozytorium nie zawiera katalogu `vendor/`, dlatego po sklonowaniu potrzebny jest Composer. Plik `.env` jest w repozytorium.

```bash
git clone https://github.com/Dawid032/e-dziennik.git wsb_2026_k37_9
cd wsb_2026_k37_9
composer install
```

Dalej postępuj jak w krokach 2–4 powyżej.

## Najczęstsze problemy

| Objaw | Przyczyna i rozwiązanie |
|---|---|
| `Unknown database 'wsb_2026_k37_9'` | Baza nie została zaimportowana. Wykonaj krok 3. |
| `Connection refused` | MySQL nie działa. Włącz go w panelu XAMPP. |
| `could not find driver` | Wyłączone rozszerzenie `pdo_mysql`. Włącz je w `php.ini`. |
| Komunikat o zbyt niskiej wersji PHP | Aplikacja wymaga PHP 8.2 lub nowszego. |
| Podstrony zwracają 404 z Apache | Sprawdź, czy istnieje plik `public/.htaccess` i czy adres zawiera nazwę folderu. |
| Komunikat „Zbyt wiele prób logowania" | Zabezpieczenie: po 5 błędnych hasłach do tego samego konta logowanie blokuje się na minutę. Udane logowania się nie liczą. |
| Błąd zapisu w `storage` (macOS, Linux) | Wykonaj polecenie `chmod` z sekcji powyżej. |

## Dokumentacja

- [Opis projektu i funkcji](docs/dokumentacja.md)
- [Przewodnik po kodzie](docs/przewodnik-po-kodzie.md)
