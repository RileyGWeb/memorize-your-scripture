<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake mail to capture email sending
        Mail::fake();
        Notification::fake();
    }

    public function test_forgot_password_page_loads(): void
    {
        $response = $this->get('/forgot-password');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.forgot-password');
        $response->assertSee('Forgot your password?');
        $response->assertSee('Email Password Reset Link');
    }

    public function test_forgot_password_link_displays_on_login_page(): void
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('Forgot your password?');
        $response->assertSee(route('password.request'));
    }

    public function test_password_reset_email_can_be_sent(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status');
        
        // Check that a password reset token was created
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_password_reset_email_validation(): void
    {
        // Test with invalid email
        $response = $this->post('/forgot-password', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);

        // Test with empty email
        $response = $this->post('/forgot-password', [
            'email' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_password_reset_email_for_nonexistent_user(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // Fortify shows error for nonexistent user (this is the actual behavior)
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        
        // And no token should be created
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'nonexistent@example.com',
        ]);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('old-password'),
        ]);

        // Create a password reset token
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status');

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
        
        // Verify old password no longer works
        $this->assertFalse(Hash::check('old-password', $user->password));
        
        // Follow the redirect and verify user is authenticated
        $this->followRedirects($response);
        $this->assertAuthenticated();
    }

    public function test_password_reset_page_loads_with_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get("/reset-password/{$token}?email={$user->email}");

        $response->assertStatus(200);
        $response->assertViewIs('auth.reset-password');
        $response->assertSee('Reset Password');
        $response->assertSee('value="' . $user->email . '"', false);
    }

    public function test_password_reset_validation(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        // Test password confirmation mismatch
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);

        // Test weak password
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
    }

    public function test_password_reset_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_password_reset_with_expired_token(): void
    {
        $user = User::factory()->create();
        
        // Create token manually in the past
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('expired-token'),
            'created_at' => now()->subHours(2), // Assuming 1 hour expiry
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'expired-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_rate_limiting_for_password_reset_requests(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Make multiple requests rapidly
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/forgot-password', [
                'email' => 'test@example.com',
            ]);
        }

        // Laravel's rate limiting returns 302 with validation errors instead of 429
        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    public function test_password_reset_link_can_only_be_used_once(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::createToken($user);

        // First use should work
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status');

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));

        // Second use should fail (token is consumed)
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'another-password',
            'password_confirmation' => 'another-password',
        ]);

        $response->assertStatus(302);
        // Token should be invalid now, so no status message
        $response->assertSessionMissing('status');
        
        // Password should remain unchanged
        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertFalse(Hash::check('another-password', $user->password));
    }

    public function test_user_is_redirected_after_successful_password_reset(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status');
        
        // Follow redirects and verify user ends up authenticated
        $this->followRedirects($response);
        $this->assertAuthenticated();
    }

    public function test_password_reset_token_cleanup(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        // Verify token exists
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);

        // Reset password
        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        // Verify token is cleaned up
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }
}
