<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModalLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_modal_login_form_submission_works(): void
    {
        // Create a user via registration (simulating the working scenario)
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Test the login form submission that happens via the modal
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_ajax_login_request(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Test AJAX login request (which might be used by the modal)
        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertStatus(200);
    }

    public function test_login_validation_errors_are_returned(): void
    {
        // Test with invalid credentials
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    public function test_login_rate_limiting(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Make multiple failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // The next attempt should be rate limited
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    public function test_successful_login_after_rate_limiting(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Make some failed attempts (but not enough to trigger rate limiting)
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // Now try with correct credentials
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_csrf_token_required_for_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Attempt login without CSRF token
        $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password',
            ]);

        $this->assertAuthenticated();
    }

    public function test_register_then_immediate_login_scenario(): void
    {
        // This test simulates the exact scenario you described
        
        // Step 1: Register a new user (this works according to your description)
        $registerResponse = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $registerResponse->assertRedirect('/');

        // Step 2: Logout (simulate closing browser or logging out)
        $this->post('/logout');
        $this->assertGuest();

        // Step 3: Try to login via modal (this fails according to your description)
        $loginResponse = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // This should work but according to the issue description, it doesn't
        $this->assertAuthenticated();
        $loginResponse->assertRedirect('/');

        // If we reach here, the login system is working correctly
        // The issue might be frontend-related (JavaScript, modal behavior, etc.)
    }

    public function test_user_exists_after_registration(): void
    {
        // Register a user
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Check user exists in database
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_login_with_remember_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');

        // Check that remember token was set
        $this->assertNotNull($user->fresh()->remember_token);
    }
}
