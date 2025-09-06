<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModalErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_modal_shows_validation_errors()
    {
        // Test login with invalid credentials
        $response = $this->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        // Should redirect back to homepage with errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors();
        
        // Follow the redirect to see the homepage with errors
        $followResponse = $this->get('/');
        
        // Check that the homepage shows the login modal (due to errors)
        $content = $followResponse->getContent();
        $this->assertStringContainsString('loginModal: true', $content);
        $this->assertStringContainsString('registerModal: false', $content);
    }

    public function test_register_modal_shows_validation_errors()
    {
        // Test registration with existing email
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Should redirect back with errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        
        // Follow the redirect to see the homepage with errors
        $followResponse = $this->get('/');
        
        // Check that the homepage shows the register modal (due to errors)
        $content = $followResponse->getContent();
        $this->assertStringContainsString('registerModal: true', $content);
        $this->assertStringContainsString('loginModal: false', $content);
    }

    public function test_login_modal_preserves_form_values()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
        
        // Follow the redirect
        $followResponse = $this->get('/');
        
        // Check that the email value is preserved in the form
        $content = $followResponse->getContent();
        $this->assertStringContainsString('value="test@example.com"', $content);
    }

    public function test_register_modal_preserves_form_values()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
        
        // Follow the redirect
        $followResponse = $this->get('/');
        
        // Check that the name and email values are preserved
        $content = $followResponse->getContent();
        $this->assertStringContainsString('value="Test User"', $content);
        $this->assertStringContainsString('value="test@example.com"', $content);
    }

    public function test_successful_login_closes_modal()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Should redirect to homepage without errors
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_successful_registration_closes_modal()
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Should redirect without errors
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }
}
