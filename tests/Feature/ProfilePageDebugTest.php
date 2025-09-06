<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_debug_content(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/user/profile');

        $response->assertStatus(200);
        
        // Debug: Show the actual content
        $content = $response->getContent();
        
        // Find all dark: classes with full context
        preg_match_all('/dark:[a-zA-Z0-9-]+/', $content, $matches);
        
        if (!empty($matches[0])) {
            $uniqueClasses = array_unique($matches[0]);
            $this->fail('Found dark mode classes: ' . implode(', ', $uniqueClasses));
        }
        
        $this->assertTrue(true);
    }
}
