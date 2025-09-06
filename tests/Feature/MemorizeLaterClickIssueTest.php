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

        // Test when showOnMemorizationTool is true (where clicking should work)
        $component = Livewire::actingAs($user)
            ->test(MemorizeLaterList::class, ['showOnMemorizationTool' => true])
            ->call('selectVerse', $verse->id)
            ->assertRedirect('/memorization-tool/display');

        // Check that session has the verse data
        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 16]],
        ], session('verseSelection'));
    }

    public function test_clicking_verse_on_homepage_does_not_redirect()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        // Test when showOnMemorizationTool is false (homepage)
        // The selectVerse method should only work when showOnMemorizationTool is true
        $component = Livewire::actingAs($user)
            ->test(MemorizeLaterList::class, ['showOnMemorizationTool' => false]);

        // On homepage, there should be no wire:click for selectVerse
        $component->assertDontSee('wire:click="selectVerse');
    }

    public function test_memorize_later_list_renders_with_conditional_wire_click()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        // Test that the component renders correctly with showOnMemorizationTool = true
        $component = Livewire::actingAs($user)
            ->test(MemorizeLaterList::class, ['showOnMemorizationTool' => true])
            ->assertSee('John 3:16')
            ->assertSee('Click a verse to start memorizing it!');

        // Test that the component renders correctly with showOnMemorizationTool = false
        $component = Livewire::actingAs($user)
            ->test(MemorizeLaterList::class, ['showOnMemorizationTool' => false])
            ->assertSee('John 3:16')
            ->assertDontSee('Click a verse to start memorizing it!');
    }
}
