<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemorizeLater;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemorizeLaterVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_memorize_later_component_is_invisible_when_user_has_no_verses(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/');
        
        $response->assertStatus(200);
        // Component should render but be empty (just an empty div)
        $response->assertSeeLivewire('memorize-later-list');
        // Should not see the "Memorize Later..." header
        $response->assertDontSee('Memorize Later...');
        // Should not see any verse-related content
        $response->assertDontSee('No verses saved yet');
    }

    public function test_memorize_later_component_is_visible_when_user_has_verses(): void
    {
        $user = User::factory()->create();
        
        // Create a verse for the user
        MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'note' => 'Test note',
            'added_at' => now(),
        ]);
        
        $response = $this->actingAs($user)->get('/');
        
        $response->assertStatus(200);
        // Component should render and be visible
        $response->assertSeeLivewire('memorize-later-list');
        // Should see the "Memorize Later..." header
        $response->assertSee('Memorize Later...');
        // Should see the verse reference
        $response->assertSee('John 3:16');
    }

    public function test_guest_user_does_not_see_memorize_later_component(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        // Component should render but be empty
        $response->assertSeeLivewire('memorize-later-list');
        // Should not see any content since user is not authenticated
        $response->assertDontSee('Memorize Later...');
        $response->assertDontSee('No verses saved yet');
    }
}
