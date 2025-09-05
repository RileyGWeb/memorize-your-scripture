<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StreakVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_streak_only_shows_on_welcome_card_when_explicitly_enabled()
    {
        $user = User::factory()->create([
            'login_streak' => 5,
            'last_login_date' => now()->toDateString()
        ]);
        
        // Create a memorized verse so the daily quiz shows
        MemoryBank::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [[16, 16]],
        ]);
        
        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200)
            // Should see streak on welcome card
            ->assertSeeText('Welcome, ' . $user->name . '!')
            ->assertSee('Streak: 5 days')
            // Should see daily quiz title but not duplicate streak
            ->assertSeeText('Daily Quiz!')
            // Verify there's only one streak display
            ->assertSeeTextInOrder([
                'Welcome, ' . $user->name . '!',
                'Streak: 5 days',
                'Daily Quiz!'
            ]);
            
        // Count occurrences of "Streak: 5 days" - should only be 1
        $content = $response->getContent();
        $streakOccurrences = substr_count($content, 'Streak: 5 days');
        $this->assertEquals(1, $streakOccurrences, 'Streak should only appear once on the page');
    }

    public function test_streak_does_not_show_when_parameter_not_provided()
    {
        $user = User::factory()->create([
            'login_streak' => 3,
            'last_login_date' => now()->toDateString()
        ]);
        
        $this->actingAs($user);

        // Test a content card title without the streak parameter
        $view = view('components.content-card-title', [
            'title' => 'Test Title',
            'subtitle' => 'Test Subtitle'
            // No :streak parameter
        ]);

        $rendered = $view->render();
        
        $this->assertStringNotContainsString('Streak:', $rendered);
        $this->assertStringContainsString('Test Title', $rendered);
        $this->assertStringContainsString('Test Subtitle', $rendered);
    }
}
