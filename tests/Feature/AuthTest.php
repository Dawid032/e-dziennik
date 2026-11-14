<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public static function roles(): array
    {
        return [
            'admin' => ['admin', '/admin/dashboard'],
            'teacher' => ['teacher', '/teacher/dashboard'],
            'student' => ['student', '/student/dashboard'],
            'parent' => ['parent', '/parent/dashboard'],
        ];
    }

    #[DataProvider('roles')]
    public function test_each_role_is_redirected_to_its_own_panel(string $role, string $path): void
    {
        $user = $this->makeUser($role);

        $this->post('/login', ['email' => $user->email, 'password' => 'haslo123'])->assertRedirect($path);
        $this->assertAuthenticatedAs($user);
        $this->get($path)->assertOk();
    }

    public function test_wrong_password_is_rejected_with_a_general_message(): void
    {
        $user = $this->makeUser('student');

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'zlehaslo1'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Nieprawidłowy e-mail lub hasło.']);

        $this->assertGuest();
    }

    public function test_login_attempts_are_limited(): void
    {
        $user = $this->makeUser('student');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'zlehaslo1']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'zlehaslo1'])->assertStatus(429);
    }

    public function test_there_is_no_public_registration(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
        $this->get('/login')->assertOk()->assertDontSee('/register');
    }

    public function test_guest_is_sent_to_login(): void
    {
        foreach (['/', '/admin/dashboard', '/teacher/dashboard', '/student/dashboard', '/parent/dashboard', '/gradebook'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
    }

    public function test_logged_in_user_opening_login_goes_to_own_panel(): void
    {
        $this->actingAs($this->makeUser('teacher'))->get('/login')->assertRedirect('/')->assertRedirectToRoute('home');
        $this->get('/')->assertRedirect('/teacher/dashboard');
    }

    public function test_logout_ends_the_session(): void
    {
        $this->actingAs($this->makeUser('student'))->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_can_change_own_password(): void
    {
        $user = $this->makeUser('parent');

        $this->actingAs($user)->from('/account/password')->put('/account/password', [
            'current_password' => 'zlehaslo1',
            'password' => 'nowehaslo1',
            'password_confirmation' => 'nowehaslo1',
        ])->assertSessionHasErrors('current_password');

        $this->put('/account/password', [
            'current_password' => 'haslo123',
            'password' => 'nowehaslo1',
            'password_confirmation' => 'nowehaslo1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(password_verify('nowehaslo1', $user->fresh()->password));
    }

    public function test_every_role_constant_has_a_panel(): void
    {
        foreach (array_keys(User::ROLES) as $role) {
            $this->assertArrayHasKey($role, self::roles());
        }
    }
}
