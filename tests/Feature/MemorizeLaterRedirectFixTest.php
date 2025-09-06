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
            ->test(\App\Livewire\MemorizeLaterList::class, ['showOnMemorizationTool' => true])
            ->call('selectVerse', $verse->id)
            ->assertRedirect('/memorization-tool/display');

        // Verify the session data was set correctly
        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 16]],
        ], session('verseSelection'));
    }

    public function test_component_renders_with_correct_attributes()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        // Test with showOnMemorizationTool = true (should have cursor-pointer and wire:click)
        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\MemorizeLaterList::class, ['showOnMemorizationTool' => true]);

        $html = $component->payload['effects']['html'];
        
        // Should contain cursor-pointer class
        $this->assertStringContainsString('cursor-pointer', $html);
        
        // Should contain wire:click attribute
        $this->assertStringContainsString('wire:click="selectVerse(' . $verse->id . ')"', $html);

        // Test with showOnMemorizationTool = false (should NOT have cursor-pointer or wire:click)
        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\MemorizeLaterList::class, ['showOnMemorizationTool' => false]);

        $html = $component->payload['effects']['html'];
        
        // Should NOT contain cursor-pointer class on the clickable div
        // (Note: we need to be careful here as cursor-pointer might appear elsewhere)
        $this->assertStringNotContainsString('wire:click="selectVerse', $html);
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
            ->test(\App\Livewire\MemorizeLaterList::class, ['showOnMemorizationTool' => true])
            ->call('selectVerse', $verse->id)
            ->assertRedirect('/memorization-tool/display');

        // Verify the session data includes all verses as individual ranges
        $expectedRanges = [[1, 1], [2, 2], [3, 3], [4, 4], [5, 5], [6, 6]];
        $this->assertEquals([
            'book' => 'Psalms',
            'chapter' => 23,
            'verseRanges' => $expectedRanges,
        ], session('verseSelection'));
    }
}
