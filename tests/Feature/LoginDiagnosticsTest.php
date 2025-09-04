<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginDiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_diagnose_login_modal_behavior(): void
    {
        // Create a user via registration to simulate your exact scenario
        $registrationResponse = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $user = User::where('email', 'test@example.com')->first();
        
        // Log user out
        $this->post('/logout');
        $this->assertGuest();

        // Check user exists in database
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // Verify password hash
        $this->assertTrue(Hash::check('password', $user->password));

        // Test login with exact same credentials
        $loginResponse = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Debug information
        if (!$this->isAuthenticated()) {
            dump('Login failed. Session errors:', session()->get('errors'));
            dump('User password hash:', $user->password);
            dump('Hash check result:', Hash::check('password', $user->password));
            dump('User record:', $user->toArray());
        }

        $this->assertAuthenticated();
        $loginResponse->assertRedirect('/');
    }

    public function test_diagnose_case_sensitivity_issues(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Test different email cases
        $testCases = [
            'test@example.com',
            'TEST@EXAMPLE.COM',
            'Test@Example.com',
            'TeSt@ExAmPlE.cOm',
        ];

        foreach ($testCases as $emailCase) {
            $this->post('/logout');
            
            $response = $this->post('/login', [
                'email' => $emailCase,
                'password' => 'password',
            ]);

            if (!$this->isAuthenticated()) {
                dump("Failed to login with email case: {$emailCase}");
            }
        }
    }

    public function test_diagnose_password_hashing_differences(): void
    {
        // Test different ways of creating users
        
        // Method 1: Factory with Hash::make
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
        ]);

        // Method 2: Direct creation with Hash::make
        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
        ]);

        // Method 3: Via registration
        $this->post('/register', [
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $this->post('/logout');
        $user3 = User::where('email', 'user3@example.com')->first();

        $users = [$user1, $user2, $user3];
        
        foreach ($users as $index => $user) {
            $this->post('/logout');
            
            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

            if (!$this->isAuthenticated()) {
                dump("User {$index} login failed");
                dump("Email: {$user->email}");
                dump("Password hash: {$user->password}");
                dump("Hash check: " . (Hash::check('password', $user->password) ? 'true' : 'false'));
            } else {
                dump("User {$index} login successful");
            }
        }
    }

    public function test_diagnose_session_issues(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Check session before login
        dump('Session before login:', session()->all());

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Check session after login
        dump('Session after login:', session()->all());
        dump('Auth check:', auth()->check());
        dump('Auth user:', auth()->user());

        if ($response->isRedirection()) {
            dump('Redirect location:', $response->headers->get('Location'));
        }

        $this->assertAuthenticated();
    }

    public function test_diagnose_csrf_token_issues(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Test login without CSRF token
        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        dump('Response status without CSRF:', $response->status());
        
        // Test with proper CSRF
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        dump('Response status with CSRF:', $response->status());
        
        $this->assertAuthenticated();
    }

    public function test_diagnose_validation_errors(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Test with correct credentials
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        if ($response->status() !== 302) {
            dump('Unexpected response status:', $response->status());
            dump('Response content:', $response->content());
        }

        if (session()->has('errors')) {
            dump('Validation errors:', session()->get('errors')->all());
        }

        $this->assertAuthenticated();
    }

    public function test_simulate_modal_login_behavior(): void
    {
        // Simulate the exact behavior described in the issue
        
        // Step 1: Register user (this works)
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        
        $this->assertAuthenticated();
        dump('Registration successful, user authenticated');
        
        // Step 2: Logout
        $this->post('/logout');
        $this->assertGuest();
        dump('Logout successful, user is guest');
        
        // Step 3: Try to login via modal (this fails according to the issue)
        $loginResponse = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        dump('Login response status:', $loginResponse->status());
        dump('Login response headers:', $loginResponse->headers->all());
        dump('Is authenticated after login:', $this->isAuthenticated());
        
        if (!$this->isAuthenticated()) {
            dump('Login failed. Session errors:', session()->get('errors'));
            dump('All session data:', session()->all());
            
            // Check if user still exists
            $user = User::where('email', 'test@example.com')->first();
            dump('User still exists:', $user ? 'Yes' : 'No');
            if ($user) {
                dump('User data:', $user->toArray());
                dump('Password check:', Hash::check('password', $user->password));
            }
        }
        
        // Step 4: Try registering again with same credentials (this works according to the issue)
        $this->post('/logout'); // Make sure we're logged out
        
        $secondRegistrationResponse = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        
        dump('Second registration response status:', $secondRegistrationResponse->status());
        dump('Second registration errors:', session()->get('errors'));
        
        // This should fail because email already exists
        $this->assertGuest();
    }
}
