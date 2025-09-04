<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class RealRegistrationTest extends TestCase
{
    // No database traits - this will persist to the actual database

    public function test_actual_registration_without_rollback(): void
    {
        echo "\n=== REAL REGISTRATION TEST (NO ROLLBACK) ===\n";
        
        // Clean up any existing test user first
        $existingUser = User::where('email', 'real-test@example.com')->first();
        if ($existingUser) {
            $existingUser->delete();
            echo "Deleted existing test user\n";
        }
        
        $initialCount = User::count();
        echo "Initial user count: {$initialCount}\n";
        
        // Register a new user
        $response = $this->post('/register', [
            'name' => 'Real Test User',
            'email' => 'real-test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        
        echo "Registration response status: " . $response->status() . "\n";
        echo "Authenticated after registration: " . ($this->isAuthenticated() ? 'YES' : 'NO') . "\n";
        
        // Check database immediately
        $finalCount = User::count();
        echo "User count after registration: {$finalCount}\n";
        
        $user = User::where('email', 'real-test@example.com')->first();
        echo "User found: " . ($user ? 'YES' : 'NO') . "\n";
        
        if ($user) {
            echo "User ID: {$user->id}\n";
            echo "User name: {$user->name}\n";
            echo "User email: {$user->email}\n";
            echo "Created at: {$user->created_at}\n";
        }
        
        echo "=== END REAL REGISTRATION TEST ===\n";
        
        // Clean up
        if ($user) {
            $user->delete();
            echo "Cleaned up test user\n";
        }
        
        $this->assertTrue($user !== null, 'User should be created and persist in database');
    }

    public function test_register_then_check_login(): void
    {
        echo "\n=== REGISTER THEN LOGIN TEST ===\n";
        
        // Clean up
        $existingUser = User::where('email', 'register-login-test@example.com')->first();
        if ($existingUser) {
            $existingUser->delete();
        }
        
        // Step 1: Register
        echo "Step 1: Registering user...\n";
        $registerResponse = $this->post('/register', [
            'name' => 'Register Login Test',
            'email' => 'register-login-test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        
        echo "Registration status: " . $registerResponse->status() . "\n";
        echo "Authenticated after registration: " . ($this->isAuthenticated() ? 'YES' : 'NO') . "\n";
        
        // Check if user exists
        $user = User::where('email', 'register-login-test@example.com')->first();
        echo "User exists after registration: " . ($user ? 'YES' : 'NO') . "\n";
        
        // Step 2: Logout
        echo "\nStep 2: Logging out...\n";
        $this->post('/logout');
        echo "Guest after logout: " . ($this->isAuthenticated() ? 'NO' : 'YES') . "\n";
        
        // Step 3: Verify user still exists
        $userStillExists = User::where('email', 'register-login-test@example.com')->first();
        echo "User still exists after logout: " . ($userStillExists ? 'YES' : 'NO') . "\n";
        
        // Step 4: Try to login
        echo "\nStep 3: Attempting login...\n";
        $loginResponse = $this->post('/login', [
            'email' => 'register-login-test@example.com',
            'password' => 'password123',
        ]);
        
        echo "Login status: " . $loginResponse->status() . "\n";
        echo "Authenticated after login: " . ($this->isAuthenticated() ? 'YES' : 'NO') . "\n";
        
        if (session()->has('errors')) {
            echo "Login errors:\n";
            foreach (session()->get('errors')->all() as $error) {
                echo "  - {$error}\n";
            }
        }
        
        echo "=== END REGISTER THEN LOGIN TEST ===\n";
        
        // Clean up
        if ($userStillExists) {
            $userStillExists->delete();
            echo "Cleaned up test user\n";
        }
        
        $this->assertTrue($this->isAuthenticated(), 'Should be able to login after registration');
    }
}
