<?php

namespace Tests\Feature;

use App\Models\MemorizeLater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemorizeLaterBrowserClickTest extends TestCase
{
    use RefreshDatabase;

    public function test_memorization_tool_page_renders_with_clickable_verses()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        $response = $this->actingAs($user)
            ->get('/memorization-tool');

        $response->assertStatus(200);
        
        // Check that the page contains the memorize later list component
        $response->assertSee('John 3:16');
        $response->assertSee('Click a verse to start memorizing it!');
        
        // Check that the wire:click attribute is present in the HTML
        $content = $response->getContent();
        $this->assertStringContainsString('wire:click="selectVerse(' . $verse->id . ')"', $content);
    }

    public function test_component_functionality_works()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        // Test that calling selectVerse works properly
        Livewire::actingAs($user)
            ->test(\App\Livewire\MemorizeLaterList::class)
            ->call('selectVerse', $verse->id)
            ->assertRedirect('/memorization-tool/fetch-verse');

        // Verify session data is stored correctly
        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 16]],
        ], session('verseSelection'));
    }

    public function test_multiple_verses_work_correctly()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16, 17, 18],
        ]);

        // Test the selectVerse method with multiple verses
        Livewire::actingAs($user)
            ->test(\App\Livewire\MemorizeLaterList::class)
            ->call('selectVerse', $verse->id)
            ->assertRedirect('/memorization-tool/fetch-verse');

        // Check that session has the correct data
        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 16], [17, 17], [18, 18]],
        ], session('verseSelection'));
    }
}
