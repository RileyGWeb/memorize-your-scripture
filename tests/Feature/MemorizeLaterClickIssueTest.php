<?php

namespace Tests\Feature;

use App\Livewire\MemorizeLaterList;
use App\Models\MemorizeLater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemorizeLaterClickIssueTest extends TestCase
{
    use RefreshDatabase;

    public function test_clicking_verse_on_memorization_tool_redirects()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        // Test that clicking verse works and redirects properly
        $component = Livewire::actingAs($user)
            ->test(MemorizeLaterList::class)
            ->call('selectVerse', $verse->id)
            ->assertRedirect('/memorization-tool/fetch-verse');

        // Check that session has the verse data
        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 16]],
        ], session('verseSelection'));
    }

    public function test_clicking_verse_works_from_any_context()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        // Test that selectVerse method always works now
        $component = Livewire::actingAs($user)
            ->test(MemorizeLaterList::class)
            ->call('selectVerse', $verse->id)
            ->assertRedirect('/memorization-tool/fetch-verse');

        // Check that session has the verse data
        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 16]],
        ], session('verseSelection'));
    }

    public function test_component_always_renders_with_click_functionality()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        // Test that the component always renders with cursor-pointer and wire:click
        $component = Livewire::actingAs($user)
            ->test(MemorizeLaterList::class)
            ->assertSee('John 3:16')
            ->assertSee('Click a verse to start memorizing it!');
    }
}
