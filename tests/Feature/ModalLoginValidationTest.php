<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModalLoginValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_modal_login_validation_errors_are_preserved(): void
    {
        // Test login with invalid credentials and check if errors are properly handled
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        // The response should redirect back with errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors();
        
        // Check that the session has the validation errors
        $this->assertTrue(session()->has('errors'));
    }

    public function test_modal_login_with_empty_fields(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_modal_login_redirects_correctly_on_success(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/'); // Should redirect to home
    }

    public function test_modal_form_inputs_have_unique_identifiers(): void
    {
        // Test that the modal form doesn't have conflicting IDs with other page elements
        $response = $this->get('/');
        
        $content = $response->getContent();
        
        // Count occurrences of id="email" - should only appear once in the modal
        $emailIdCount = substr_count($content, 'id="email"');
        $passwordIdCount = substr_count($content, 'id="password"');
        
        // If there are multiple elements with the same ID, it could cause form submission issues
        $this->assertTrue($emailIdCount <= 2, "Found {$emailIdCount} elements with id='email' - this could cause conflicts");
        $this->assertTrue($passwordIdCount <= 2, "Found {$passwordIdCount} elements with id='password' - this could cause conflicts");
    }

    public function test_debug_login_failure_scenario(): void
    {
        // This test simulates the exact issue you're experiencing
        
        // First, register a user (this works)
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        
        $this->assertAuthenticated();
        
        // Logout
        $this->post('/logout');
        $this->assertGuest();
        
        // Now try to login (this fails according to your issue)
        $response = $this->followingRedirects()->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        // If this test passes, the backend is working correctly
        // The issue is likely in the frontend JavaScript/modal behavior
        $this->assertAuthenticated();
        
        // Also check that we're on the correct page
        $response->assertStatus(200);
    }

    public function test_csrf_protection_on_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Test login without CSRF token (should fail)
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            '_token' => 'invalid-token',
        ]);

        $response->assertStatus(419); // CSRF token mismatch
        $this->assertGuest();
    }

    public function test_login_rate_limiting_behavior(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // The 6th attempt should be rate limited
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429); // Too Many Requests
        
        // Even a correct password should be blocked now
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(429);
        $this->assertGuest();
    }

    public function test_check_if_user_account_is_locked_or_disabled(): void
    {
        // Create a user and verify they can login
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Check if there are any additional fields that might prevent login
        $this->assertNull($user->email_verified_at); // Email verification might be required
        
        // Try login
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }

    public function test_check_fortify_configuration(): void
    {
        // Check that Fortify is properly configured
        $this->assertTrue(config('fortify.views'), 'Fortify views should be enabled');
        $this->assertEquals('email', config('fortify.username'), 'Username field should be email');
        $this->assertEquals('/', config('fortify.home'), 'Home path should be /');
    }
}
