<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemorizeLater;
use Livewire\Livewire;
use App\Livewire\MemorizeLaterList;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemorizeLaterConsecutiveVersesTest extends TestCase
{
    use RefreshDatabase;

    public function test_consecutive_verses_are_grouped_into_single_range(): void
    {
        $user = User::factory()->create();
        
        // Create Isaiah 55:8,9 - the exact case that was failing
        $isaiahVerse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'Isaiah',
            'chapter' => 55,
            'verses' => [8, 9],
            'note' => 'God\'s ways vs our ways',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', $isaiahVerse->id);

        $component->assertRedirect('/memorization-tool/fetch-verse');
        
        // The key fix: consecutive verses [8, 9] should become a single range [8, 9]
        // instead of separate ranges [[8, 8], [9, 9]]
        $this->assertEquals([
            'book' => 'Isaiah',
            'chapter' => 55,
            'verseRanges' => [[8, 9]], // Single range for consecutive verses
        ], session('verseSelection'));
    }

    public function test_non_consecutive_verses_remain_separate(): void
    {
        $user = User::factory()->create();
        
        // Create verses that are NOT consecutive
        $nonConsecutiveVerse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'Psalm',
            'chapter' => 119,
            'verses' => [1, 5, 9], // Non-consecutive
        ]);

        $this->actingAs($user);

        Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', $nonConsecutiveVerse->id);
        
        // Non-consecutive verses should remain as separate ranges
        $this->assertEquals([
            'book' => 'Psalm',
            'chapter' => 119,
            'verseRanges' => [[1, 1], [5, 5], [9, 9]], // Separate ranges for non-consecutive verses
        ], session('verseSelection'));
    }

    public function test_mixed_consecutive_and_non_consecutive_verses(): void
    {
        $user = User::factory()->create();
        
        // Create a mix: 1-3 (consecutive), 7 (isolated), 10-12 (consecutive)
        $mixedVerse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'Romans',
            'chapter' => 8,
            'verses' => [1, 2, 3, 7, 10, 11, 12],
        ]);

        $this->actingAs($user);

        Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', $mixedVerse->id);
        
        // Should intelligently group consecutive verses
        $this->assertEquals([
            'book' => 'Romans',
            'chapter' => 8,
            'verseRanges' => [
                [1, 3],   // Consecutive verses 1-3 grouped
                [7, 7],   // Isolated verse 7
                [10, 12], // Consecutive verses 10-12 grouped
            ],
        ], session('verseSelection'));
    }

    public function test_unordered_consecutive_verses_are_sorted_and_grouped(): void
    {
        $user = User::factory()->create();
        
        // Create verses in random order but that are actually consecutive
        $unorderedVerse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [18, 16, 17], // Out of order but consecutive
        ]);

        $this->actingAs($user);

        Livewire::test(MemorizeLaterList::class)
            ->call('selectVerse', $unorderedVerse->id);
        
        // Should sort and group consecutive verses
        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 18]], // Sorted and grouped
        ], session('verseSelection'));
    }

    public function test_format_verse_reference_displays_correctly(): void
    {
        $user = User::factory()->create();
        
        $component = new MemorizeLaterList();
        
        // Test single verse
        $singleVerse = MemorizeLater::factory()->make([
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);
        
        $this->assertEquals('John 3:16', $component->formatVerseReference($singleVerse));
        
        // Test consecutive verses
        $consecutiveVerses = MemorizeLater::factory()->make([
            'book' => 'Isaiah',
            'chapter' => 55,
            'verses' => [8, 9],
        ]);
        
        $this->assertEquals('Isaiah 55:8-9', $component->formatVerseReference($consecutiveVerses));
        
        // Test longer range
        $longerRange = MemorizeLater::factory()->make([
            'book' => 'Psalm',
            'chapter' => 23,
            'verses' => [1, 2, 3, 4, 5, 6],
        ]);
        
        $this->assertEquals('Psalm 23:1-6', $component->formatVerseReference($longerRange));
    }
}
