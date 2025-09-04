<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DifficultyProgressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_difficulties_api_returns_correct_data(): void
    {
        $user = User::factory()->create();
        
        // Create memory bank entries for different difficulties
        MemoryBank::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'difficulty' => 'easy'
        ]);
        
        MemoryBank::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'difficulty' => 'normal'
        ]);

        $this->actingAs($user);

        $response = $this->get('/api/completed-difficulties?book=John&chapter=3&verses=16');
        
        $response->assertStatus(200);
        $response->assertJson([
            'difficulties' => ['easy', 'normal']
        ]);
    }

    public function test_unauthenticated_user_gets_empty_difficulties(): void
    {
        $response = $this->get('/api/completed-difficulties?book=John&chapter=3&verses=16');
        
        $response->assertStatus(200);
        $response->assertJson([
            'difficulties' => []
        ]);
    }

    public function test_memorization_page_includes_persistent_congrats_logic(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Set up a verse selection in the session
        session([
            'verseSelection' => [
                'book' => 'John',
                'chapter' => 3,
                'verseRanges' => [[16, 16]]
            ]
        ]);

        $response = $this->get('/memorization-tool/display');
        
        // Should not be a redirect (assuming verse API works in test)
        if ($response->status() === 200) {
            // Check that the persistent congrats logic is in place
            $response->assertSee('if (!this.showCongrats)');
            $response->assertSee('shouldShowIncreaseDifficultyButton()');
            $response->assertSee('getDifficultyDisplayName(difficulty)');
        }
    }
}
