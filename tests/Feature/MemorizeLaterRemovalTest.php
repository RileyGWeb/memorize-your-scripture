<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemorizeLater;
use App\Models\MemoryBank;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemorizeLaterRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_verses_are_removed_from_memorize_later_when_memorized(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a "Memorize Later" entry
        $memorizeLater = MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16, 17, 18],
            'note' => 'Great verses!',
            'added_at' => now(),
        ]);

        // Verify the entry exists
        $this->assertDatabaseHas('memorize_later', [
            'id' => $memorizeLater->id,
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
        ]);

        // Simulate memorizing verses 16 and 17 (but not 18)
        $response = $this->postJson('/memorization-tool/save', [
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16, 17],
            'difficulty' => 'easy',
            'accuracy_score' => 95.5,
            'bible_translation' => 'ESV',
            'user_text' => 'For God so loved the world...',
        ]);

        $response->assertStatus(200);

        // Check that a memory bank entry was created
        $this->assertDatabaseHas('memory_bank', [
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => json_encode([16, 17]),
        ]);

        // Reload the memorize later entry
        $memorizeLater->refresh();

        // The memorize later entry should still exist but only contain verse 18
        $this->assertEquals([18], $memorizeLater->verses);
    }

    public function test_entire_memorize_later_entry_is_deleted_when_all_verses_memorized(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a "Memorize Later" entry with a single verse
        $memorizeLater = MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'Psalm',
            'chapter' => 23,
            'verses' => [1],
            'note' => 'The Lord is my shepherd',
            'added_at' => now(),
        ]);

        // Verify the entry exists
        $this->assertDatabaseHas('memorize_later', [
            'id' => $memorizeLater->id,
        ]);

        // Memorize the only verse in the entry
        $response = $this->postJson('/memorization-tool/save', [
            'book' => 'Psalm',
            'chapter' => 23,
            'verses' => [1],
            'difficulty' => 'normal',
            'accuracy_score' => 88.0,
            'bible_translation' => 'ESV',
            'user_text' => 'The Lord is my shepherd, I shall not want.',
        ]);

        $response->assertStatus(200);

        // The entire memorize later entry should be deleted
        $this->assertDatabaseMissing('memorize_later', [
            'id' => $memorizeLater->id,
        ]);

        // Check that a memory bank entry was created
        $this->assertDatabaseHas('memory_bank', [
            'user_id' => $user->id,
            'book' => 'Psalm',
            'chapter' => 23,
            'verses' => json_encode([1]),
        ]);
    }

    public function test_multiple_memorize_later_entries_are_handled_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create multiple "Memorize Later" entries for the same book/chapter
        $entry1 = MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'Romans',
            'chapter' => 8,
            'verses' => [28, 29],
            'note' => 'All things work together',
            'added_at' => now(),
        ]);

        $entry2 = MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'Romans',
            'chapter' => 8,
            'verses' => [28, 30, 31],
            'note' => 'Different grouping',
            'added_at' => now(),
        ]);

        // Memorize verse 28
        $response = $this->postJson('/memorization-tool/save', [
            'book' => 'Romans',
            'chapter' => 8,
            'verses' => [28],
            'difficulty' => 'strict',
            'accuracy_score' => 92.0,
            'bible_translation' => 'ESV',
            'user_text' => 'And we know that for those who love God...',
        ]);

        $response->assertStatus(200);

        // Reload entries
        $entry1->refresh();
        $entry2->refresh();

        // Entry 1 should only have verse 29 left
        $this->assertEquals([29], $entry1->verses);

        // Entry 2 should have verses 30 and 31 left
        $this->assertEquals([30, 31], $entry2->verses);
    }

    public function test_memorizing_verse_from_different_book_doesnt_affect_other_entries(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create "Memorize Later" entries for different books
        $johnEntry = MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'added_at' => now(),
        ]);

        $psalmsEntry = MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'Psalms',
            'chapter' => 23,
            'verses' => [1],
            'added_at' => now(),
        ]);

        // Memorize John 3:16
        $response = $this->postJson('/memorization-tool/save', [
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'difficulty' => 'easy',
            'accuracy_score' => 95.0,
            'bible_translation' => 'ESV',
            'user_text' => 'For God so loved the world...',
        ]);

        $response->assertStatus(200);

        // John entry should be deleted (all verses memorized)
        $this->assertDatabaseMissing('memorize_later', [
            'id' => $johnEntry->id,
        ]);

        // Psalms entry should remain unchanged
        $this->assertDatabaseHas('memorize_later', [
            'id' => $psalmsEntry->id,
            'book' => 'Psalms',
            'chapter' => 23,
        ]);
    }
}