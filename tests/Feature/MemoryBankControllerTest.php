<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\MemoryBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MemoryBankControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test memory bank index displays user's memorized verses.
     *
     * @test
     */
    public function memory_bank_index_displays_users_memorized_verses()
    {
        // Create memory bank entries for this user
        $memoryEntry1 = MemoryBank::factory()->create([
            'user_id' => $this->user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'difficulty' => 'easy',
        ]);

        $memoryEntry2 = MemoryBank::factory()->create([
            'user_id' => $this->user->id,
            'book' => 'Romans',
            'chapter' => 8,
            'verses' => [28],
            'difficulty' => 'normal',
        ]);

        // Create entry for different user (should not show)
        $otherUser = User::factory()->create();
        MemoryBank::factory()->create([
            'user_id' => $otherUser->id,
            'book' => 'Matthew',
            'chapter' => 5,
            'verses' => [3, 4, 5],
        ]);

        $response = $this->actingAs($this->user)
            ->get('/bank');

        $response->assertOk();
        $response->assertViewIs('bank');
        $response->assertViewHas('items');

        $items = $response->viewData('items');
        $this->assertCount(2, $items);
        $this->assertTrue($items->contains('id', $memoryEntry1->id));
        $this->assertTrue($items->contains('id', $memoryEntry2->id));
    }

    /**
     * Test unauthenticated user cannot access memory bank.
     *
     * @test
     */
    public function unauthenticated_user_cannot_access_memory_bank()
    {
        $response = $this->get('/bank');
        
        $response->assertRedirect('/login');
    }

    /**
     * Test fetch verse text with single verse.
     *
     * @test
     */
    public function fetch_verse_text_with_single_verse()
    {
        Http::fake([
            'api.scripture.api.bible/*' => Http::response([
                'data' => [
                    [
                        'content' => '<p><span class="verse-num">16</span>For God so loved the world, that he gave his only Son, that whoever believes in him should not perish but have eternal life.</p>'
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
            ->get('/bank/fetch-verse', [
                'book' => 'John',
                'chapter' => 3,
                'verses' => [16],
                'bible_translation' => 'de4e12af7f28f599-02'
            ]);

        $response->assertOk();
        $response->assertJson([
            'reference' => 'John 3:16',
            'bible_translation' => 'de4e12af7f28f599-02'
        ]);

        $response->assertJsonStructure([
            'reference',
            'verse_text',
            'bible_translation'
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.scripture.api.bible') &&
                   $request->hasHeader('api-key') &&
                   $request['reference'] === 'John 3:16';
        });
    }

    /**
     * Test fetch verse text with continuous verse range.
     *
     * @test
     */
    public function fetch_verse_text_with_continuous_verse_range()
    {
        Http::fake([
            'api.scripture.api.bible/*' => Http::response([
                'data' => [
                    [
                        'content' => '<p>Continuous verses content</p>'
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
            ->get('/bank/fetch-verse', [
                'book' => 'Romans',
                'chapter' => 8,
                'verses' => [28, 29, 30],
            ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'reference' => 'Romans 8:28-30'
        ]);

        Http::assertSent(function ($request) {
            return $request['reference'] === 'Romans 8:28-30';
        });
    }

    /**
     * Test fetch verse text with non-continuous verses.
     *
     * @test
     */
    public function fetch_verse_text_with_non_continuous_verses()
    {
        Http::fake([
            'api.scripture.api.bible/*' => Http::response([
                'data' => [
                    [
                        'content' => '<p>Non-continuous verses content</p>'
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
            ->get('/bank/fetch-verse', [
                'book' => 'Psalm',
                'chapter' => 23,
                'verses' => [1, 4, 6],
            ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'reference' => 'Psalm 23:1,4,6'
        ]);

        Http::assertSent(function ($request) {
            return $request['reference'] === 'Psalm 23:1,4,6';
        });
    }

    /**
     * Test fetch verse text validation errors.
     *
     * @test
     */
    public function fetch_verse_text_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->get('/bank/fetch-verse', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['book', 'chapter', 'verses']);
    }

    /**
     * Test fetch verse text validation for invalid types.
     *
     * @test
     */
    public function fetch_verse_text_validates_field_types()
    {
        $response = $this->actingAs($this->user)
            ->get('/bank/fetch-verse', [
                'book' => 123, // should be string
                'chapter' => 'invalid', // should be integer
                'verses' => 'not-array', // should be array
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['book', 'chapter', 'verses']);
    }

    /**
     * Test fetch verse text handles API failure.
     *
     * @test
     */
    public function fetch_verse_text_handles_api_failure()
    {
        Http::fake([
            'api.scripture.api.bible/*' => Http::response([], 500)
        ]);

        $response = $this->actingAs($this->user)
            ->get('/bank/fetch-verse', [
                'book' => 'John',
                'chapter' => 3,
                'verses' => [16],
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Failed to fetch verse text.'
        ]);
    }

    /**
     * Test fetch verse text uses default bible translation.
     *
     * @test
     */
    public function fetch_verse_text_uses_default_bible_translation()
    {
        config(['bible.default' => 'test-bible-id']);

        Http::fake([
            'api.scripture.api.bible/*' => Http::response([
                'data' => [
                    [
                        'content' => '<p>Test content</p>'
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
            ->get('/bank/fetch-verse', [
                'book' => 'John',
                'chapter' => 3,
                'verses' => [16],
                // No bible_translation provided
            ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'bible_translation' => 'test-bible-id'
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'test-bible-id');
        });
    }

    /**
     * Test fetch verse text strips HTML tags from response.
     *
     * @test
     */
    public function fetch_verse_text_strips_html_tags()
    {
        Http::fake([
            'api.scripture.api.bible/*' => Http::response([
                'data' => [
                    [
                        'content' => '<p><span class="verse-num">16</span>For God so loved <em>the world</em>, that he gave his only Son.</p>'
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
            ->get('/bank/fetch-verse', [
                'book' => 'John',
                'chapter' => 3,
                'verses' => [16],
            ]);

        $response->assertOk();
        
        $verseText = $response->json('verse_text');
        $this->assertStringNotContainsString('<p>', $verseText);
        $this->assertStringNotContainsString('<span>', $verseText);
        $this->assertStringNotContainsString('<em>', $verseText);
        $this->assertStringContainsString('For God so loved', $verseText);
    }

    /**
     * Test search verses returns results when query provided.
     *
     * @test
     */
    public function search_verses_returns_results_when_query_provided()
    {
        $response = $this->actingAs($this->user)
            ->get('/bank/search-verses?q=love');

        $response->assertOk();
        $response->assertJsonStructure([
            'items' => [
                '*' => [
                    'id',
                    'book',
                    'chapter',
                    'verses',
                    'difficulty',
                    'memorized_at',
                    'verse_text'
                ]
            ]
        ]);

        $items = $response->json('items');
        $this->assertNotEmpty($items);
        $this->assertEquals('John', $items[0]['book']);
        $this->assertEquals(3, $items[0]['chapter']);
        $this->assertEquals([16], $items[0]['verses']);
    }

    /**
     * Test search verses returns empty when no query provided.
     *
     * @test
     */
    public function search_verses_returns_empty_when_no_query_provided()
    {
        $response = $this->actingAs($this->user)
            ->get('/bank/search-verses');

        $response->assertOk();
        $response->assertJson([
            'items' => []
        ]);
    }

    /**
     * Test search verses returns empty when empty query provided.
     *
     * @test
     */
    public function search_verses_returns_empty_when_empty_query_provided()
    {
        $response = $this->actingAs($this->user)
            ->get('/bank/search-verses?q=');

        $response->assertOk();
        $response->assertJson([
            'items' => []
        ]);
    }

    /**
     * Test unauthenticated user cannot fetch verse text.
     *
     * @test
     */
    public function unauthenticated_user_cannot_fetch_verse_text()
    {
        $response = $this->get('/bank/fetch-verse', [
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test unauthenticated user cannot search verses.
     *
     * @test
     */
    public function unauthenticated_user_cannot_search_verses()
    {
        $response = $this->get('/bank/search-verses?q=love');

        $response->assertRedirect('/login');
    }

    /**
     * Test memory bank index shows items in correct order.
     *
     * @test
     */
    public function memory_bank_index_shows_items_in_correct_order()
    {
        // Create entries with different dates
        $older = MemoryBank::factory()->create([
            'user_id' => $this->user->id,
            'memorized_at' => now()->subDays(2),
            'book' => 'Older',
        ]);

        $newer = MemoryBank::factory()->create([
            'user_id' => $this->user->id,
            'memorized_at' => now()->subDay(),
            'book' => 'Newer',
        ]);

        $newest = MemoryBank::factory()->create([
            'user_id' => $this->user->id,
            'memorized_at' => now(),
            'book' => 'Newest',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/memory-bank');

        $response->assertOk();
        
        $items = $response->viewData('items');
        $this->assertEquals('Newest', $items->first()->book);
        $this->assertEquals('Older', $items->last()->book);
    }
}
