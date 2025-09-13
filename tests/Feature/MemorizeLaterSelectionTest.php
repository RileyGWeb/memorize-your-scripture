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

        // Should redirect to memorization tool fetch (which then redirects to display)
        $component->assertRedirect('/memorization-tool/fetch-verse');
        
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
        
        // Create a memorize later entry with consecutive verses using the factory
        $memorizeLater = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'Romans',
            'chapter' => 8,
            'verses' => [28, 29, 30],
        ]);

        $this->actingAs($user);

        $component = Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', $memorizeLater->id);

        // Should redirect to memorization tool fetch (which then redirects to display)
        $component->assertRedirect('/memorization-tool/fetch-verse');
        
        // Should have set the verse selection in session with consecutive verses grouped into a single range
        $this->assertEquals([
            'book' => 'Romans',
            'chapter' => 8,
            'verseRanges' => [[28, 30]], // Consecutive verses should be grouped
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

    public function test_verse_range_logic_with_various_scenarios(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test 1: Single verse
        $singleVerse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', $singleVerse->id);

        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 16]],
        ], session('verseSelection'));

        // Test 2: Consecutive verses (should be grouped)
        $consecutiveVerses = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'Isaiah',
            'chapter' => 55,
            'verses' => [8, 9],
        ]);

        Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', $consecutiveVerses->id);

        $this->assertEquals([
            'book' => 'Isaiah',
            'chapter' => 55,
            'verseRanges' => [[8, 9]], // Consecutive verses grouped
        ], session('verseSelection'));

        // Test 3: Non-consecutive verses (should create separate ranges)
        $nonConsecutiveVerses = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'Psalm',
            'chapter' => 23,
            'verses' => [1, 3, 5],
        ]);

        Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', $nonConsecutiveVerses->id);

        $this->assertEquals([
            'book' => 'Psalm',
            'chapter' => 23,
            'verseRanges' => [[1, 1], [3, 3], [5, 5]], // Non-consecutive stay separate
        ], session('verseSelection'));

        // Test 4: Mixed consecutive and non-consecutive verses
        $mixedVerses = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'Romans',
            'chapter' => 8,
            'verses' => [28, 29, 30, 35, 38, 39],
        ]);

        Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', $mixedVerses->id);

        $this->assertEquals([
            'book' => 'Romans',
            'chapter' => 8,
            'verseRanges' => [[28, 30], [35, 35], [38, 39]], // Smart grouping
        ], session('verseSelection'));
    }
}
