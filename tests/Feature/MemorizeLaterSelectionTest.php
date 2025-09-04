<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemorizeLater;
use Livewire\Livewire;
use App\Livewire\MemorizeLaterList;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemorizeLaterSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_selecting_memorize_later_verse_redirects_to_memorization_tool(): void
    {
        $user = User::factory()->create();
        
        // Create a memorize later entry using the factory
        $memorizeLater = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        $this->actingAs($user);

        $component = Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', $memorizeLater->id);

        // Should redirect to memorization tool display
        $component->assertRedirect('/memorization-tool/display');
        
        // Should have set the verse selection in session
        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 16]],
        ], session('verseSelection'));
    }

    public function test_selecting_memorize_later_verse_with_multiple_verses(): void
    {
        $user = User::factory()->create();
        
        // Create a memorize later entry with multiple verses using the factory
        $memorizeLater = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'Romans',
            'chapter' => 8,
            'verses' => [28, 29, 30],
        ]);

        $this->actingAs($user);

        $component = Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', $memorizeLater->id);

        // Should redirect to memorization tool display
        $component->assertRedirect('/memorization-tool/display');
        
        // Should have set the verse selection in session with individual verse ranges
        $this->assertEquals([
            'book' => 'Romans',
            'chapter' => 8,
            'verseRanges' => [[28, 28], [29, 29], [30, 30]],
        ], session('verseSelection'));
    }

    public function test_selecting_nonexistent_verse_does_not_redirect(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', 999); // Non-existent ID

        // Should not redirect
        $component->assertNoRedirect();
        
        // Should not set anything in session
        $this->assertNull(session('verseSelection'));
    }
}
