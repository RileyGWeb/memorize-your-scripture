<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserStreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_has_zero_streak(): void
    {
        $user = User::factory()->create();
        
        $this->assertEquals(0, $user->login_streak);
        $this->assertNull($user->last_login_date);
    }

    public function test_first_login_sets_streak_to_one(): void
    {
        $user = User::factory()->create();
        
        $user->updateLoginStreak();
        
        $this->assertEquals(1, $user->fresh()->login_streak);
        $this->assertEquals(now()->toDateString(), $user->fresh()->last_login_date->toDateString());
    }

    public function test_consecutive_daily_logins_increase_streak(): void
    {
        $user = User::factory()->create();
        
        // Day 1
        Carbon::setTestNow('2025-01-01 10:00:00');
        $user->updateLoginStreak();
        $this->assertEquals(1, $user->fresh()->login_streak);
        
        // Day 2
        Carbon::setTestNow('2025-01-02 15:00:00');
        $user->updateLoginStreak();
        $this->assertEquals(2, $user->fresh()->login_streak);
        
        // Day 3
        Carbon::setTestNow('2025-01-03 09:00:00');
        $user->updateLoginStreak();
        $this->assertEquals(3, $user->fresh()->login_streak);
    }

    public function test_multiple_logins_same_day_dont_increase_streak(): void
    {
        $user = User::factory()->create();
        
        Carbon::setTestNow('2025-01-01 10:00:00');
        $user->updateLoginStreak();
        $this->assertEquals(1, $user->fresh()->login_streak);
        
        // Later the same day
        Carbon::setTestNow('2025-01-01 18:00:00');
        $user->updateLoginStreak();
        $this->assertEquals(1, $user->fresh()->login_streak);
    }

    public function test_skipping_a_day_resets_streak(): void
    {
        $user = User::factory()->create();
        
        // Day 1
        Carbon::setTestNow('2025-01-01 10:00:00');
        $user->updateLoginStreak();
        $this->assertEquals(1, $user->fresh()->login_streak);
        
        // Day 2
        Carbon::setTestNow('2025-01-02 15:00:00');
        $user->updateLoginStreak();
        $this->assertEquals(2, $user->fresh()->login_streak);
        
        // Skip day 3, login on day 4
        Carbon::setTestNow('2025-01-04 09:00:00');
        $user->updateLoginStreak();
        $this->assertEquals(1, $user->fresh()->login_streak); // Reset to 1
    }

    public function test_login_streak_display_method_returns_correct_values(): void
    {
        $user = User::factory()->create();
        
        // No streak
        $this->assertNull($user->getLoginStreakForDisplay());
        
        // Streak of 1
        $user->login_streak = 1;
        $user->save();
        $this->assertNull($user->getLoginStreakForDisplay()); // Don't show 1-day streaks
        
        // Streak of 2+
        $user->login_streak = 5;
        $user->save();
        $this->assertEquals(5, $user->getLoginStreakForDisplay());
    }

    public function test_unauthenticated_user_has_no_streak_display(): void
    {
        $this->assertNull(auth()->user());
        
        // This would be called in the component
        $streak = auth()->check() ? auth()->user()->getLoginStreakForDisplay() : null;
        $this->assertNull($streak);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset time mocking
        parent::tearDown();
    }
}
