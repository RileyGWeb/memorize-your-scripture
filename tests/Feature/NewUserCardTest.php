<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class NewUserCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_card_shows_for_guest_users(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('New here?', false);
        $response->assertSee('Click "memorize scripture" to get started!', false);
    }

    public function test_new_user_card_does_not_show_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertDontSee('New here?', false);
        $response->assertDontSee('Click "memorize scripture" to get started!', false);
    }

    public function test_new_user_card_can_be_dismissed(): void
    {
        // First visit - card should show
        $response = $this->get('/');
        $response->assertSee('New here?', false);
        
        // Dismiss the card
        $response = $this->post('/dismiss-new-user-card');
        $response->assertStatus(200);
        
        // Second visit - card should not show
        $response = $this->get('/');
        $response->assertDontSee('New here?', false);
    }

    public function test_how_to_get_started_button_is_removed(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertDontSee('How to get started');
    }

    public function test_dismissed_card_shows_again_after_session_clear(): void
    {
        // Dismiss the card
        $this->post('/dismiss-new-user-card');
        
        // Card should not show
        $response = $this->get('/');
        $response->assertDontSee('New here?', false);
        
        // Create a fresh test instance to simulate cleared session
        $freshResponse = $this->app['request']->create('/', 'GET');
        $response = $this->get('/');
        
        // The card should show again since we're making a new request without the dismiss session
        $this->refreshApplication();
        $response = $this->get('/');
        $response->assertSee('New here?', false);
    }
}
