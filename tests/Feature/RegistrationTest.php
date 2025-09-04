<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_registration_creates_user_in_database(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_registration_fails_with_existing_email(): void
    {
        // Create a user first
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_registration_fails_with_password_mismatch(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_registration_then_immediate_login_works(): void
    {
        // Register a new user
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        
        // Log out
        $this->post('/logout');
        $this->assertGuest();

        // Try to login immediately
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_user_password_hashing_consistency_between_registration_and_manual_creation(): void
    {
        // Create user via registration
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'registered@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $registeredUser = User::where('email', 'registered@example.com')->first();

        // Create user manually
        $manualUser = User::factory()->create([
            'email' => 'manual@example.com',
            'password' => Hash::make('password'),
        ]);

        // Both should be able to login
        $this->post('/logout');
        
        $response1 = $this->post('/login', [
            'email' => 'registered@example.com',
            'password' => 'password',
        ]);
        $this->assertAuthenticated();

        $this->post('/logout');

        $response2 = $this->post('/login', [
            'email' => 'manual@example.com',
            'password' => 'password',
        ]);
        $this->assertAuthenticated();
    }

    public function test_registration_modal_form_exists_on_homepage(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Register');
        $response->assertSee('action="' . route('register') . '"', false);
    }

    public function test_re_registration_with_same_credentials_after_login_logout(): void
    {
        // Register a user
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        
        // Log out
        $this->post('/logout');
        $this->assertGuest();

        // Try to register again with same credentials (should fail)
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}
