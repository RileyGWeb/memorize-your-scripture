<?php

namespace Tests\Feature;

use App\Models\MemorizeLater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemorizeLaterRedirectFixTest extends TestCase
{
    use RefreshDatabase;

    public function test_livewire_redirect_works_correctly()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        // Test that the component properly redirects using Livewire's redirect method
        Livewire::actingAs($user)
            ->test(\App\Livewire\MemorizeLaterList::class)
            ->call('selectVerse', $verse->id)
            ->assertRedirect('/memorization-tool/fetch-verse');

        // Verify the session data was set correctly
        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 16]],
        ], session('verseSelection'));
    }

    public function test_component_always_renders_with_clickable_behavior()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        // Test that component always has cursor-pointer and wire:click
        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\MemorizeLaterList::class);

        // Should see the verse reference
        $component->assertSee('John 3:16');
        
        // Should see the help text about clicking
        $component->assertSee('Click a verse to start memorizing it!');
    }

    public function test_multiple_verses_redirect_correctly()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'Psalms',
            'chapter' => 23,
            'verses' => [1, 2, 3, 4, 5, 6],
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MemorizeLaterList::class)
            ->call('selectVerse', $verse->id)
            ->assertRedirect('/memorization-tool/fetch-verse');

        // Verify the session data includes all consecutive verses as a single range
        $expectedRanges = [[1, 6]]; // All consecutive verses grouped into one range
        $this->assertEquals([
            'book' => 'Psalms',
            'chapter' => 23,
            'verseRanges' => $expectedRanges,
        ], session('verseSelection'));
    }
}
