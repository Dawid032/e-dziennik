# Przewodnik po kodzie (ściąga na obronę bez internetu)

Wszystkie polecenia uruchamiaj w głównym folderze projektu. Żadne z nich nie wymaga internetu ani Composera. Na komputerze z XAMPP-em zamiast `php` może być potrzebna pełna ścieżka, np. `C:\xampp\php\php.exe`.

## Co gdzie jest

| Czego szukasz | Gdzie |
|---|---|
| Adresy i kto ma do nich dostęp | `routes/web.php` |
| Logika stron | `app/Http/Controllers/` |
| Walidacja formularzy | `app/Http/Requests/` |
| Wyrażenia regularne | `app/Support/ValidationPatterns.php` |
| Tabele jako klasy, relacje, uprawnienia | `app/Models/` (najważniejszy jest `User.php`) |
| Sprawdzanie roli | `app/Http/Middleware/EnsureUserHasRole.php` |
| Historia ocen | `app/Observers/GradeObserver.php` |
| Średnia ważona | `app/Services/GradeAverageCalculator.php` |
| Widoki | `resources/views/` |
| Pola formularzy | `resources/views/components/` (`<x-input>`, `<x-select>`, `<x-textarea>`) |
| Menu boczne | `resources/views/partials/nav.blade.php` |
| Wygląd | `public/css/app.css` |
| JavaScript | `public/js/app.js` |
| Polskie komunikaty | `lang/pl/validation.php` |
| Liczba wyników, kategorie ocen | `config/dziennik.php` |
| Struktura bazy | `database/migrations/` |
| Dane demo | `database/seeders/DatabaseSeeder.php` |

## Przepływ żądania

```text
Przeglądarka
  → routes/web.php            (który kontroler?)
  → middleware auth i role:   (czy wolno wejść?)
  → Form Request              (czy dane są poprawne? tylko przy zapisie)
  → kontroler                 (co zrobić?)
  → model Eloquent            (odczyt lub zapis w bazie)
  → widok Blade               (HTML)
```

## Podział na trzy obszary

Każda osoba powinna umieć omówić i zmienić swój obszar.

**A. Logowanie, role, panel admina**
`LoginController`, `AccountController`, `DashboardController`, `EnsureUserHasRole`, `AdminDashboardController`, `AdminUserController`, `SchoolClassController`, `SubjectController`, `TeachingAssignmentController`, `routes/web.php`, widoki `auth/`, `account/`, `admin/`.

**B. Oceny, historia, średnia**
`GradeController`, `BulkGradeController`, `GradeHistoryController`, `TeacherDashboardController`, modele `Grade` i `GradeHistory`, `GradeObserver`, `GradeAverageCalculator`, `GradeRequest`, `BulkGradeRequest`, widoki `gradebook/`, `history/`, `teacher/dashboard`.

**C. Uwagi, panele ucznia i rodzica, walidacja i regexy**
`NoteController`, `HomeroomController`, `StudentDashboardController`, `ParentDashboardController`, model `Note`, `ValidationPatterns`, pozostałe Form Requests, `lang/pl/`, komponenty formularzy, widoki `notes/`, `student/`, `parent/`, `teacher/homeroom/`, `partials/`.

## Przepisy

### Nowa kolumna i pole formularza

Przykład: telefon użytkownika.

1. Utwórz migrację:

   ```bash
   php artisan make:migration add_phone_to_users_table
   ```

2. W nowym pliku w `database/migrations/`:

   ```php
   // w metodzie up()
   Schema::table('users', function (Blueprint $table) {
       $table->string('phone', 20)->nullable()->after('email');
   });

   // w metodzie down()
   Schema::table('users', function (Blueprint $table) {
       $table->dropColumn('phone');
   });
   ```

3. Wykonaj migrację:

   ```bash
   php artisan migrate
   ```

4. W `app/Models/User.php` dopisz `'phone'` do tablicy `$fillable`.
5. W `app/Http/Requests/UserRequest.php` dopisz w `rules()`:

   ```php
   'phone' => ['nullable', 'string', 'max:20'],
   ```

6. W `resources/views/admin/users/_form.blade.php` dodaj w `form-grid`:

   ```blade
   <x-input name="phone" label="Telefon" :value="$user->phone" maxlength="20" />
   ```

7. W `resources/views/admin/users/show.blade.php` dodaj w liście `dl`:

   ```blade
   <dt>Telefon</dt><dd>{{ $user->phone ?? '—' }}</dd>
   ```

8. W `lang/pl/validation.php` dopisz w `attributes`: `'phone' => 'telefon',`.

### Nowa reguła walidacji z polskim komunikatem

Przykład: telefon z dziewięciu cyfr.

1. W `app/Support/ValidationPatterns.php` dodaj stałą:

   ```php
   public const PHONE = '^\\d{9}$';
   ```

2. W `UserRequest` dopisz regułę do pola:

   ```php
   'phone' => ['nullable', 'string', ValidationPatterns::rule(ValidationPatterns::PHONE)],
   ```

3. W `lang/pl/validation.php`, w sekcji `custom`:

   ```php
   'phone' => ['regex' => 'Telefon musi składać się z 9 cyfr.'],
   ```

4. W formularzu dodaj atrybut `:pattern="Patterns::PHONE"` do `<x-input>`.

W klasach znaków `[...]` zapisuj myślnik i nawiasy jako `\-`, `\(`, `\)`. Inaczej przeglądarka uzna wzorzec za błędny i przestanie sprawdzać pole.

### Nowa kategoria oceny

W `config/dziennik.php` dopisz nazwę do `grade_categories`, np. `'Projekt'`. Formularze i walidacja zmienią się same. Migracja nie jest potrzebna.

### Zmiana liczby wyników na stronie

W `config/dziennik.php` zmień `per_page`.

### Nowe pole w wyszukiwarce

W `app/Models/User.php`, w metodzie `scopeSearch`, dopisz warunek w środku funkcji:

```php
->orWhere('phone', 'like', "%{$word}%");
```

Pamiętaj, że średnik ma być tylko po ostatnim warunku.

### Nowa strona

Przykład: strona „O szkole" dla zalogowanych.

1. Utwórz kontroler:

   ```bash
   php artisan make:controller AboutController
   ```

2. W `app/Http/Controllers/AboutController.php`:

   ```php
   public function index()
   {
       return view('about');
   }
   ```

3. W `routes/web.php`, wewnątrz grupy `auth`, oraz na górze pliku `use App\Http\Controllers\AboutController;`:

   ```php
   Route::get('/about', [AboutController::class, 'index'])->name('about');
   ```

4. Utwórz `resources/views/about.blade.php`:

   ```blade
   @extends('layouts.app')

   @section('title', 'O szkole')

   @section('content')
       <div class="page-head"><h1>O szkole</h1></div>
       <div class="card">Treść strony.</div>
   @endsection
   ```

5. W `resources/views/partials/nav.blade.php` dopisz do tablicy wybranej roli:

   ```php
   ['O szkole', route('about'), ['about']],
   ```

Adresy zawsze przez `route()`, `url()` lub `asset()`. Adres zaczynający się od `/` nie zadziała w podfolderze XAMPP-a.

### Nowa rola

Przykład: sekretariat (`secretary`).

1. Utwórz migrację rozszerzającą listę ról:

   ```bash
   php artisan make:migration add_secretary_role_to_users_table
   ```

   ```php
   // w metodzie up(); na górze pliku: use Illuminate\Support\Facades\DB;
   DB::statement("ALTER TABLE users MODIFY role ENUM('admin','teacher','student','parent','secretary') NOT NULL");
   ```

2. Wykonaj `php artisan migrate`.
3. W `app/Models/User.php` dodaj stałą `ROLE_SECRETARY = 'secretary'` i wpis w `ROLES`.
4. W `routes/web.php` dodaj grupę z `role:secretary` i trasą o nazwie `secretary.dashboard`. Nazwa jest ważna, bo przekierowanie po logowaniu składa się z nazwy roli i `.dashboard`.
5. Utwórz kontroler i widok pulpitu, jak w przepisie „Nowa strona".
6. W `partials/nav.blade.php` dodaj do `match` przypadek `'secretary' => [ ... ]`. Bez tego strona zgłosi błąd `UnhandledMatchError`.

## Polecenia działające offline

| Polecenie | Do czego służy |
|---|---|
| `php artisan serve` | uruchamia aplikację pod `http://127.0.0.1:8000` |
| `php artisan route:list` | pokazuje wszystkie trasy |
| `php artisan migrate` | wykonuje nowe migracje |
| `php artisan migrate:fresh --seed` | kasuje bazę i tworzy ją od nowa z danymi demo |
| `php artisan make:controller NazwaController` | tworzy kontroler |
| `php artisan make:model Nazwa -m` | tworzy model z migracją |
| `php artisan make:request NazwaRequest` | tworzy Form Request |
| `php artisan make:migration nazwa` | tworzy migrację |
| `php artisan tinker` | konsola do sprawdzania modeli |
| `php artisan optimize:clear` | czyści pamięć podręczną konfiguracji, tras i widoków |

Nie używaj `php artisan config:cache` ani `route:cache`, bo zapisują ścieżki bezwzględne.

## Typowe błędy

| Błąd | Co sprawdzić |
|---|---|
| `Class "..." not found` | Czy na górze pliku jest `use`, a nazwa i wielkość liter się zgadzają. |
| `Route [...] not defined` | Nazwa trasy w `routes/web.php`. Pomaga `php artisan route:list`. |
| `View [...] not found` | Ścieżkę widoku: kropka to katalog, `gradebook.sheet` to `gradebook/sheet.blade.php`. |
| `Add [pole] to fillable` | Dopisz pole do `$fillable` w modelu. |
| `Column not found` | Czy migracja została wykonana (`php artisan migrate`). |
| `419 Sesja wygasła` | Czy formularz ma `@csrf`. |
| `405 Method Not Allowed` | Czy formularz PUT lub DELETE ma `@method('PUT')` albo `@method('DELETE')`. |
| Pole znika z bazy po zapisie | Czy jest w `rules()` w Form Requeście, bo zapisywane są tylko pola walidowane. |
| Zmiana nie jest widoczna | `php artisan optimize:clear` i odświeżenie strony. |
| „Zbyt wiele prób logowania” | 5 błędnych haseł do jednego konta w ciągu minuty. Odczekaj minutę albo wykonaj `php artisan cache:clear`. Limit jest w `LoginController@store`. |
