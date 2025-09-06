<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_without_dark_mode_classes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/user/profile');

        $response->assertStatus(200);
        
        // Ensure we don't have dark mode classes in the main content
        $response->assertDontSee('dark:text-gray-200', false);
        $response->assertDontSee('dark:bg-gray-800', false);
    }

    public function test_profile_page_is_mobile_responsive(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/user/profile');

        $response->assertStatus(200);
        
        // Check for mobile responsive classes
        $response->assertSee('sm:px-6', false);
        $response->assertSee('lg:px-8', false);
        $response->assertSee('sm:col-span-4', false);
    }

    public function test_profile_page_does_not_show_browser_sessions_section(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/user/profile');

        $response->assertStatus(200);
        
        // Browser sessions section should not be present
        $response->assertDontSee('Browser Sessions');
        $response->assertDontSee('Manage and log out your active sessions');
        $response->assertDontSee('Log Out Other Browser Sessions');
    }

    public function test_profile_page_shows_background_image_change_option(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/user/profile');

        $response->assertStatus(200);
        
        // Should show background image change section
        $response->assertSee('Background Image');
        $response->assertSee('Change your profile background');
    }

    public function test_unauthenticated_user_cannot_access_profile(): void
    {
        $response = $this->get('/user/profile');

        $response->assertRedirect('/login');
    }

    public function test_profile_page_has_proper_mobile_layout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/user/profile');

        $response->assertStatus(200);
        
        // Check for proper mobile spacing and container classes
        $response->assertSee('max-w-7xl mx-auto', false);
        $response->assertSee('py-10', false);
    }
}
