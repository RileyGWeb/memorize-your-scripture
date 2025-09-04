<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StreakDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_card_title_shows_streak_for_logged_in_user_with_streak(): void
    {
        $user = User::factory()->create();
        $user->login_streak = 5;
        $user->last_login_date = now()->toDateString();
        $user->save();
        
        $response = $this->actingAs($user)->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('Streak: 5 days');
    }

    public function test_content_card_title_does_not_show_streak_for_user_with_zero_streak(): void
    {
        $user = User::factory()->create();
        $user->login_streak = 0;
        $user->save();
        
        $response = $this->actingAs($user)->get('/');
        
        $response->assertStatus(200);
        $response->assertDontSee('Streak:');
    }

    public function test_content_card_title_does_not_show_streak_for_user_with_one_day_streak(): void
    {
        $user = User::factory()->create();
        $user->login_streak = 1;
        $user->last_login_date = now()->toDateString();
        $user->save();
        
        $response = $this->actingAs($user)->get('/');
        
        $response->assertStatus(200);
        $response->assertDontSee('Streak:');
    }

    public function test_content_card_title_does_not_show_streak_for_guest_users(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertDontSee('Streak:');
    }

    public function test_streak_updates_on_login(): void
    {
        $user = User::factory()->create([
            'login_streak' => 2,
            'last_login_date' => now()->subDay()->toDateString(),
        ]);
        
        // Login should trigger streak update
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        
        $user->refresh();
        $this->assertEquals(3, $user->login_streak);
        $this->assertEquals(now()->toDateString(), $user->last_login_date);
    }

    public function test_streak_resets_after_missing_days(): void
    {
        $user = User::factory()->create([
            'login_streak' => 5,
            'last_login_date' => now()->subDays(3)->toDateString(), // 3 days ago
        ]);
        
        // Login should reset streak
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        
        $user->refresh();
        $this->assertEquals(1, $user->login_streak); // Reset to 1
        $this->assertEquals(now()->toDateString(), $user->last_login_date);
    }
}
