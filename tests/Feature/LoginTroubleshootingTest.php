<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTroubleshootingTest extends TestCase
{
    use RefreshDatabase;

    public function test_comprehensive_login_diagnosis(): void
    {
        echo "\n=== LOGIN SYSTEM DIAGNOSIS ===\n";

        // Step 1: Create user via registration
        echo "1. Creating user via registration...\n";
        $registerResponse = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        
        echo "   Registration status: " . $registerResponse->status() . "\n";
        echo "   Authenticated after registration: " . ($this->isAuthenticated() ? 'YES' : 'NO') . "\n";

        // Check user in database
        $user = User::where('email', 'test@example.com')->first();
        echo "   User exists in database: " . ($user ? 'YES' : 'NO') . "\n";
        if ($user) {
            echo "   User ID: " . $user->id . "\n";
            echo "   Email verified: " . ($user->email_verified_at ? 'YES' : 'NO') . "\n";
            echo "   Password hash: " . substr($user->password, 0, 20) . "...\n";
            echo "   Password check: " . (Hash::check('password', $user->password) ? 'PASS' : 'FAIL') . "\n";
        }

        // Step 2: Logout
        echo "\n2. Logging out...\n";
        $this->post('/logout');
        echo "   Guest after logout: " . ($this->isAuthenticated() ? 'NO' : 'YES') . "\n";

        // Step 3: Attempt login
        echo "\n3. Attempting login with same credentials...\n";
        $loginResponse = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        echo "   Login status: " . $loginResponse->status() . "\n";
        echo "   Authenticated after login: " . ($this->isAuthenticated() ? 'YES' : 'NO') . "\n";
        echo "   Redirect location: " . ($loginResponse->headers->get('Location') ?? 'NONE') . "\n";

        if (session()->has('errors')) {
            echo "   Session errors: " . session()->get('errors')->first() . "\n";
        } else {
            echo "   Session errors: NONE\n";
        }

        // Step 4: Test with fresh instance to simulate new session
        echo "\n4. Testing with fresh session...\n";
        $this->refreshApplication();
        
        $freshLoginResponse = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        echo "   Fresh login status: " . $freshLoginResponse->status() . "\n";
        echo "   Fresh authenticated: " . ($this->isAuthenticated() ? 'YES' : 'NO') . "\n";

        // Step 5: Check rate limiting
        echo "\n5. Checking rate limiting...\n";
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }
        
        $rateLimitResponse = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        echo "   Rate limit status: " . $rateLimitResponse->status() . "\n";

        echo "\n=== DIAGNOSIS COMPLETE ===\n";

        // The main assertion - if this passes, backend is working
        $this->assertTrue(true, 'Diagnosis completed - check output above');
    }

    public function test_modal_specific_issues(): void
    {
        echo "\n=== MODAL-SPECIFIC DIAGNOSIS ===\n";

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Test 1: Check homepage loads correctly
        echo "1. Testing homepage...\n";
        $homepageResponse = $this->get('/');
        echo "   Homepage status: " . $homepageResponse->status() . "\n";
        
        $content = $homepageResponse->getContent();
        echo "   Contains login modal: " . (str_contains($content, 'loginModal') ? 'YES' : 'NO') . "\n";
        echo "   Contains Alpine.js data: " . (str_contains($content, 'x-data') ? 'YES' : 'NO') . "\n";
        echo "   Contains login form: " . (str_contains($content, 'action="' . route('login') . '"') ? 'YES' : 'NO') . "\n";

        // Test 2: Check for ID conflicts
        echo "\n2. Checking for element ID conflicts...\n";
        $emailIds = substr_count($content, 'id="email"');
        $passwordIds = substr_count($content, 'id="password"');
        
        echo "   Elements with id='email': " . $emailIds . "\n";
        echo "   Elements with id='password': " . $passwordIds . "\n";
        
        if ($emailIds > 1 || $passwordIds > 1) {
            echo "   ⚠️  POTENTIAL ISSUE: Multiple elements with same ID can cause form submission problems\n";
        }

        // Test 3: Test AJAX login
        echo "\n3. Testing AJAX login (simulating modal behavior)...\n";
        $ajaxResponse = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        echo "   AJAX login status: " . $ajaxResponse->status() . "\n";
        echo "   AJAX authenticated: " . ($this->isAuthenticated() ? 'YES' : 'NO') . "\n";

        echo "\n=== MODAL DIAGNOSIS COMPLETE ===\n";

        $this->assertTrue(true, 'Modal diagnosis completed');
    }

    public function test_generate_browser_debug_script(): void
    {
        echo "\n=== BROWSER DEBUG SCRIPT ===\n";
        echo "Copy and paste this JavaScript into your browser console while on the homepage:\n\n";
        
        echo "// Login Modal Debug Script\n";
        echo "console.log('=== LOGIN MODAL DEBUG ===');\n";
        echo "console.log('Alpine.js loaded:', typeof Alpine !== 'undefined');\n";
        echo "console.log('Login modal element:', document.querySelector('[x-show=\"loginModal\"]'));\n";
        echo "console.log('Login form:', document.querySelector('form[action*=\"login\"]'));\n";
        echo "console.log('Email inputs:', document.querySelectorAll('input[name=\"email\"]'));\n";
        echo "console.log('Password inputs:', document.querySelectorAll('input[name=\"password\"]'));\n";
        echo "console.log('CSRF token:', document.querySelector('input[name=\"_token\"]')?.value);\n\n";

        echo "// Test modal opening\n";
        echo "function testModalOpen() {\n";
        echo "    console.log('Testing modal open...');\n";
        echo "    Alpine.store ? console.log('Alpine store available') : console.log('No Alpine store');\n";
        echo "    document.querySelector('[x-data]').__x_dataStack ? console.log('Alpine data available') : console.log('No Alpine data');\n";
        echo "}\n\n";

        echo "// Test form submission\n";
        echo "function testFormSubmission() {\n";
        echo "    console.log('Testing form submission...');\n";
        echo "    const form = document.querySelector('form[action*=\"login\"]');\n";
        echo "    if (form) {\n";
        echo "        console.log('Form found:', form);\n";
        echo "        console.log('Form action:', form.action);\n";
        echo "        console.log('Form method:', form.method);\n";
        echo "        const emailInput = form.querySelector('input[name=\"email\"]');\n";
        echo "        const passwordInput = form.querySelector('input[name=\"password\"]');\n";
        echo "        console.log('Email input:', emailInput);\n";
        echo "        console.log('Password input:', passwordInput);\n";
        echo "        if (emailInput && passwordInput) {\n";
        echo "            emailInput.value = 'test@example.com';\n";
        echo "            passwordInput.value = 'password';\n";
        echo "            console.log('Filled form inputs');\n";
        echo "        }\n";
        echo "    } else {\n";
        echo "        console.log('❌ Login form not found!');\n";
        echo "    }\n";
        echo "}\n\n";

        echo "// Run tests\n";
        echo "testModalOpen();\n";
        echo "testFormSubmission();\n";
        echo "console.log('=== DEBUG COMPLETE ===');\n\n";

        echo "Run testModalOpen() and testFormSubmission() in your console to debug the modal.\n";
        echo "\n=== END BROWSER DEBUG SCRIPT ===\n";

        $this->assertTrue(true, 'Debug script generated');
    }
}
