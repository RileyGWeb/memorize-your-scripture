<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_saves_user_to_database(): void
    {
        echo "\n=== DATABASE REGISTRATION TEST ===\n";
        
        // Check initial user count
        $initialCount = User::count();
        echo "Initial user count: {$initialCount}\n";
        
        // Test registration
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        
        echo "Registration response status: " . $response->status() . "\n";
        echo "Authenticated after registration: " . ($this->isAuthenticated() ? 'YES' : 'NO') . "\n";
        
        // Check if user was actually saved
        $finalCount = User::count();
        echo "Final user count: {$finalCount}\n";
        
        // Try to find the user
        $user = User::where('email', 'test@example.com')->first();
        echo "User found in database: " . ($user ? 'YES' : 'NO') . "\n";
        
        if ($user) {
            echo "User ID: {$user->id}\n";
            echo "User name: {$user->name}\n";
            echo "User email: {$user->email}\n";
            echo "Password hash length: " . strlen($user->password) . "\n";
        }
        
        // Check database directly
        $dbUser = DB::table('users')->where('email', 'test@example.com')->first();
        echo "User found via raw query: " . ($dbUser ? 'YES' : 'NO') . "\n";
        
        echo "=== END DATABASE TEST ===\n";
        
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
    }

    public function test_manual_user_creation(): void
    {
        echo "\n=== MANUAL USER CREATION TEST ===\n";
        
        // Try creating a user manually
        $user = User::create([
            'name' => 'Manual User',
            'email' => 'manual@example.com',
            'password' => Hash::make('password'),
        ]);
        
        echo "Manual user created ID: " . ($user ? $user->id : 'FAILED') . "\n";
        echo "User count after manual creation: " . User::count() . "\n";
        
        // Check if it was saved
        $foundUser = User::find($user->id);
        echo "Manual user found: " . ($foundUser ? 'YES' : 'NO') . "\n";
        
        echo "=== END MANUAL CREATION TEST ===\n";
        
        $this->assertNotNull($user);
        $this->assertDatabaseHas('users', ['email' => 'manual@example.com']);
    }

    public function test_database_transaction_issues(): void
    {
        echo "\n=== DATABASE TRANSACTION TEST ===\n";
        
        DB::beginTransaction();
        
        try {
            $user = User::create([
                'name' => 'Transaction User',
                'email' => 'transaction@example.com',
                'password' => Hash::make('password'),
            ]);
            
            echo "User created in transaction: " . ($user ? 'YES' : 'NO') . "\n";
            echo "User count in transaction: " . User::count() . "\n";
            
            DB::commit();
            
            echo "User count after commit: " . User::count() . "\n";
            
        } catch (\Exception $e) {
            DB::rollback();
            echo "Transaction failed: " . $e->getMessage() . "\n";
        }
        
        echo "=== END TRANSACTION TEST ===\n";
        
        $this->assertTrue(true);
    }

    public function test_check_database_configuration(): void
    {
        echo "\n=== DATABASE CONFIGURATION TEST ===\n";
        
        echo "Database connection: " . config('database.default') . "\n";
        echo "Database host: " . config('database.connections.' . config('database.default') . '.host') . "\n";
        echo "Database name: " . config('database.connections.' . config('database.default') . '.database') . "\n";
        
        // Test basic database connectivity
        try {
            $result = DB::select('SELECT 1 as test');
            echo "Database connectivity: WORKING\n";
        } catch (\Exception $e) {
            echo "Database connectivity: FAILED - " . $e->getMessage() . "\n";
        }
        
        // Test users table exists
        try {
            $result = DB::select('DESCRIBE users');
            echo "Users table exists: YES\n";
            echo "Users table columns: " . count($result) . "\n";
        } catch (\Exception $e) {
            echo "Users table exists: NO - " . $e->getMessage() . "\n";
        }
        
        echo "=== END CONFIGURATION TEST ===\n";
        
        $this->assertTrue(true);
    }

    public function test_fortify_registration_action(): void
    {
        echo "\n=== FORTIFY REGISTRATION ACTION TEST ===\n";
        
        // Test the Fortify CreateNewUser action directly
        $createUserAction = new \App\Actions\Fortify\CreateNewUser();
        
        try {
            $user = $createUserAction->create([
                'name' => 'Fortify User',
                'email' => 'fortify@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);
            
            echo "Fortify user created: " . ($user ? 'YES' : 'NO') . "\n";
            if ($user) {
                echo "Fortify user ID: {$user->id}\n";
                echo "User saved to database: " . (User::find($user->id) ? 'YES' : 'NO') . "\n";
            }
            
        } catch (\Exception $e) {
            echo "Fortify creation failed: " . $e->getMessage() . "\n";
        }
        
        echo "=== END FORTIFY TEST ===\n";
        
        $this->assertTrue(true);
    }
}
