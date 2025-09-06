<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_renders_successfully()
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('About Me');
        $response->assertSee('Memorize Your Scripture');
    }

    public function test_about_page_contains_placeholder_image()
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        // Check for the SVG placeholder icon
        $response->assertSee('M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z');
    }

    public function test_about_page_contains_scripture_reference()
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('Psalm 119:11');
        $response->assertSee('I have hidden your word in my heart');
    }

    public function test_about_page_is_accessible_without_authentication()
    {
        $response = $this->get('/about');
        
        $response->assertStatus(200);
    }

    public function test_about_page_is_accessible_with_authentication()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/about');
        
        $response->assertStatus(200);
        $response->assertSee('About Me');
    }

    public function test_about_page_navigation_link_exists()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('href="http://localhost/about"', false);
    }
}
