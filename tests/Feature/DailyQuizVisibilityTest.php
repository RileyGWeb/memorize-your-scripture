<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DailyQuizVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_quiz_card_hidden_when_user_has_no_verses()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertDontSee('Daily Quiz!')
            ->assertDontSee('Daily juice to keep those verses in your brain');
    }

    public function test_daily_quiz_card_visible_when_user_has_verses()
    {
        $user = User::factory()->create();
        
        // Create a memorized verse for the user
        MemoryBank::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [[16, 16]],
        ]);
        
        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Daily Quiz!')
            ->assertSee('Daily juice to keep those verses in your brain');
    }

    public function test_daily_quiz_card_hidden_for_guest_users()
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertDontSee('Daily Quiz!')
            ->assertDontSee('Daily juice to keep those verses in your brain');
    }
}
