<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemorizeLater;
use Livewire\Livewire;
use App\Livewire\MemorizeLaterList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class MemorizeLaterUXImprovementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_memorize_later_verses_do_not_show_remove_button()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        $component = Livewire::actingAs($user)
            ->test(MemorizeLaterList::class);

        // Should see the verse
        $component->assertSee('John 3:16');
        
        // Should NOT see any remove/X buttons
        $component->assertDontSee('Remove');
        $component->assertDontSee('×');
        $component->assertDontSee('removeVerse');
    }

    public function test_verse_shows_relative_time_instead_of_date()
    {
        $user = User::factory()->create();
        
        // Test the component method directly
        $component = new MemorizeLaterList();
        
        // Test 2 hours ago
        $date = Carbon::now()->subHours(2);
        $result = $component->formatRelativeDate($date);
        $this->assertEquals('2 hours ago', $result);
        
        // Test that it's not showing the old date format
        $this->assertNotEquals($date->format('n/j/y'), $result);
    }

    public function test_verse_shows_days_ago_for_older_verses()
    {
        $user = User::factory()->create();
        
        // Test the component method directly
        $component = new MemorizeLaterList();
        
        // Test 3 days ago
        $date = Carbon::now()->subDays(3);
        $result = $component->formatRelativeDate($date);
        $this->assertEquals('3 days ago', $result);
    }

    public function test_verse_shows_minutes_ago_for_recent_verses()
    {
        $user = User::factory()->create();
        
        // Test the component method directly
        $component = new MemorizeLaterList();
        
        // Test 30 minutes ago
        $date = Carbon::now()->subMinutes(30);
        $result = $component->formatRelativeDate($date);
        $this->assertEquals('30 minutes ago', $result);
    }

    public function test_verse_with_note_shows_note_and_relative_time()
    {
        $user = User::factory()->create();
        
        // Test the component method directly
        $component = new MemorizeLaterList();
        
        // Test 1 day ago
        $date = Carbon::now()->subDay();
        $result = $component->formatRelativeDate($date);
        $this->assertEquals('1 day ago', $result);
        
        // Create a verse to test UI integration as well
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'Matthew',
            'chapter' => 5,
            'verses' => [14],
            'note' => 'This is about being the light of the world',
            'added_at' => Carbon::now()->subDay(),
        ]);

        $componentTest = Livewire::actingAs($user)
            ->test(MemorizeLaterList::class);

        // Should show the note and see that it has a relative date (not the old format)
        $componentTest->assertSee('This is about being the light of the world');
        $componentTest->assertSee('Note -');
        $componentTest->assertDontSee(Carbon::now()->subDay()->format('n/j/y'));
    }

    public function test_remove_verse_method_is_removed_from_component()
    {
        $user = User::factory()->create();
        $component = new MemorizeLaterList();

        // Check that the removeVerse method doesn't exist
        $this->assertFalse(method_exists($component, 'removeVerse'), 
            'The removeVerse method should be removed from the MemorizeLaterList component');
    }
}
