<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModalUserExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_with_existing_email_shows_error_in_modal()
    {
        // Create an existing user
        User::factory()->create(['email' => 'existing@example.com']);

        // Attempt to register with the same email
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Should redirect back with errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);

        // Get the homepage to see the modal state
        $homepageResponse = $this->get('/');
        $content = $homepageResponse->getContent();

        // The register modal should be open (true) due to errors
        $this->assertStringContainsString('registerModal: true', $content);
        
        // Should show the error message
        $this->assertStringContainsString('The email has already been taken', $content);
        
        // Should preserve the form values
        $this->assertStringContainsString('value="New User"', $content);
        $this->assertStringContainsString('value="existing@example.com"', $content);
    }

    public function test_login_with_nonexistent_account_shows_error_in_modal()
    {
        // Attempt to login with non-existent credentials
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        // Should redirect back with errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        // Get the homepage to see the modal state
        $homepageResponse = $this->get('/');
        $content = $homepageResponse->getContent();

        // The login modal should be open (true) due to errors
        $this->assertStringContainsString('loginModal: true', $content);
        
        // Should show error messages
        $this->assertStringContainsString('These credentials do not match our records', $content);
        
        // Should preserve the email value
        $this->assertStringContainsString('value="nonexistent@example.com"', $content);
    }

    public function test_login_with_wrong_password_shows_error_in_modal()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correctpassword'),
        ]);

        // Attempt to login with wrong password
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Should redirect back with errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        // Get the homepage to see the modal state
        $homepageResponse = $this->get('/');
        $content = $homepageResponse->getContent();

        // The login modal should be open due to errors
        $this->assertStringContainsString('loginModal: true', $content);
        
        // Should preserve the email value
        $this->assertStringContainsString('value="test@example.com"', $content);
    }

    public function test_register_with_password_mismatch_shows_error_in_modal()
    {
        // Attempt to register with mismatched passwords
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        // Should redirect back with errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);

        // Get the homepage to see the modal state
        $homepageResponse = $this->get('/');
        $content = $homepageResponse->getContent();

        // The register modal should be open due to errors
        $this->assertStringContainsString('registerModal: true', $content);
        
        // Verify error message is displayed in modal
        $this->assertStringContainsString('The password field confirmation does not match', $content);
        
        // Should preserve the form values (except passwords)
        $this->assertStringContainsString('value="Test User"', $content);
        $this->assertStringContainsString('value="test@example.com"', $content);
    }

    public function test_successful_operations_do_not_show_modals()
    {
        // Test successful registration
        $response = $this->post('/register', [
            'name' => 'Success User',
            'email' => 'success@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();

        // Get the homepage - modals should be closed
        $homepageResponse = $this->get('/');
        $content = $homepageResponse->getContent();

        // Both modals should be closed (false)
        $this->assertStringContainsString('loginModal: false', $content);
        $this->assertStringContainsString('registerModal: false', $content);
    }
}
