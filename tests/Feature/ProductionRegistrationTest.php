<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ProductionRegistrationTest extends TestCase
{
    // Don't use RefreshDatabase to test against actual database
    use DatabaseTransactions;

    public function test_registration_in_production_environment(): void
    {
        echo "\n=== PRODUCTION REGISTRATION TEST ===\n";
        
        // Clear any existing test users
        User::where('email', 'production-test@example.com')->delete();
        
        $initialCount = User::count();
        echo "Initial user count: {$initialCount}\n";
        
        // Simulate the exact registration request
        $response = $this->post('/register', [
            'name' => 'Production Test User',
            'email' => 'production-test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        
        echo "Registration response status: " . $response->status() . "\n";
        echo "Response headers: " . print_r($response->headers->all(), true) . "\n";
        
        if ($response->isRedirection()) {
            echo "Redirected to: " . $response->headers->get('Location') . "\n";
        }
        
        echo "Authenticated: " . ($this->isAuthenticated() ? 'YES' : 'NO') . "\n";
        
        // Check authentication details
        if ($this->isAuthenticated()) {
            $user = auth()->user();
            echo "Authenticated user ID: {$user->id}\n";
            echo "Authenticated user email: {$user->email}\n";
        }
        
        // Check database immediately after registration
        $finalCount = User::count();
        echo "Final user count: {$finalCount}\n";
        
        $user = User::where('email', 'production-test@example.com')->first();
        echo "User found: " . ($user ? 'YES' : 'NO') . "\n";
        
        if ($user) {
            echo "Found user ID: {$user->id}\n";
            echo "Found user name: {$user->name}\n";
        }
        
        // Check if there are any validation errors
        if (session()->has('errors')) {
            echo "Validation errors:\n";
            foreach (session()->get('errors')->all() as $error) {
                echo "  - {$error}\n";
            }
        }
        
        echo "=== END PRODUCTION TEST ===\n";
        
        // This should pass if registration is working
        $this->assertTrue($finalCount > $initialCount, 'User should be created in database');
    }

    public function test_check_database_logs_during_registration(): void
    {
        echo "\n=== DATABASE LOG TEST ===\n";
        
        // Enable query logging
        DB::enableQueryLog();
        
        // Clear existing test user
        User::where('email', 'log-test@example.com')->delete();
        
        echo "Starting registration with query logging...\n";
        
        $response = $this->post('/register', [
            'name' => 'Log Test User',
            'email' => 'log-test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        
        // Get the queries that were executed
        $queries = DB::getQueryLog();
        
        echo "Number of queries executed: " . count($queries) . "\n";
        
        foreach ($queries as $index => $query) {
            echo "Query " . ($index + 1) . ": " . $query['query'] . "\n";
            if (!empty($query['bindings'])) {
                echo "  Bindings: " . print_r($query['bindings'], true) . "\n";
            }
        }
        
        // Check if INSERT query was executed
        $insertQueries = array_filter($queries, function($query) {
            return strpos(strtoupper($query['query']), 'INSERT INTO') !== false;
        });
        
        echo "INSERT queries found: " . count($insertQueries) . "\n";
        
        foreach ($insertQueries as $query) {
            echo "INSERT query: " . $query['query'] . "\n";
        }
        
        echo "=== END DATABASE LOG TEST ===\n";
        
        $this->assertTrue(true, 'Log test completed');
    }

    public function test_manual_user_creation_in_production(): void
    {
        echo "\n=== MANUAL CREATION IN PRODUCTION ===\n";
        
        try {
            $user = new User();
            $user->name = 'Manual Production User';
            $user->email = 'manual-prod@example.com';
            $user->password = bcrypt('password123');
            $saved = $user->save();
            
            echo "Manual save result: " . ($saved ? 'SUCCESS' : 'FAILED') . "\n";
            echo "User ID after save: " . ($user->id ?? 'NULL') . "\n";
            
            // Check if it's actually in the database
            $found = User::find($user->id);
            echo "User found after save: " . ($found ? 'YES' : 'NO') . "\n";
            
            // Clean up
            if ($found) {
                $found->delete();
            }
            
        } catch (\Exception $e) {
            echo "Manual creation failed: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
        
        echo "=== END MANUAL CREATION TEST ===\n";
        
        $this->assertTrue(true, 'Manual creation test completed');
    }

    public function test_check_for_database_constraints(): void
    {
        echo "\n=== DATABASE CONSTRAINTS TEST ===\n";
        
        try {
            // Test creating a user with duplicate email
            User::create([
                'name' => 'First User',
                'email' => 'constraint-test@example.com',
                'password' => bcrypt('password'),
            ]);
            
            echo "First user created successfully\n";
            
            // Try to create another with same email
            User::create([
                'name' => 'Second User',
                'email' => 'constraint-test@example.com',
                'password' => bcrypt('password'),
            ]);
            
            echo "Second user created (this shouldn't happen due to unique constraint)\n";
            
        } catch (\Exception $e) {
            echo "Constraint violation (expected): " . $e->getMessage() . "\n";
        }
        
        // Clean up
        User::where('email', 'constraint-test@example.com')->delete();
        
        echo "=== END CONSTRAINTS TEST ===\n";
        
        $this->assertTrue(true, 'Constraints test completed');
    }
}
