<?php

namespace Tests\Feature;

use App\Models\MemorizeLater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_direct_verse_selection_via_post_request()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        // Simulate what happens when Livewire makes a request
        $response = $this->actingAs($user)
            ->post('/livewire/update', [
                'fingerprint' => [
                    'id' => 'memorize-later-list',
                    'name' => 'memorize-later-list',
                    'locale' => 'en',
                    'path' => '/memorization-tool',
                    'method' => 'GET',
                    'v' => 'acj',
                ],
                'serverMemo' => [
                    'children' => [],
                    'errors' => [],
                    'htmlHash' => 'test',
                    'data' => [
                        'showOnMemorizationTool' => true,
                    ],
                    'dataMeta' => [],
                    'checksum' => 'test',
                ],
                'updates' => [
                    [
                        'type' => 'callMethod',
                        'payload' => [
                            'id' => uniqid(),
                            'method' => 'selectVerse',
                            'params' => [$verse->id],
                        ],
                    ],
                ],
            ]);

        // This should result in a redirect response
        $this->assertEquals(302, $response->status());
    }

    public function test_session_stores_verse_selection_correctly()
    {
        $user = User::factory()->create();
        $verse = MemorizeLater::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16, 17, 18],
        ]);

        // Manually call the selectVerse method
        $component = new \App\Livewire\MemorizeLaterList();
        $component->showOnMemorizationTool = true;
        
        // Simulate authentication
        auth()->login($user);
        
        $result = $component->selectVerse($verse->id);
        
        // Check that session has the correct data
        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 16], [17, 17], [18, 18]],
        ], session('verseSelection'));
        
        // Check that it returns a redirect
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);
        $this->assertEquals('/memorization-tool/display', $result->getTargetUrl());
    }
}
