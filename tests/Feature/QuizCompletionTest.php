<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuizCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        // Create some memory bank entries for testing
        MemoryBank::factory()
            ->for($this->user)
            ->count(3)
            ->create();
    }

        /** @test */
    public function quiz_mode_shows_next_verse_button_in_completion_screen()
    {
        // Create memory bank entries for the quiz
        $memoryBank1 = MemoryBank::factory()->create([
            'user_id' => $this->user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => '[[16, 17]]'
        ]);

        $memoryBank2 = MemoryBank::factory()->create([
            'user_id' => $this->user->id,
            'book' => 'Romans',
            'chapter' => 8,
            'verses' => '[[28, 29]]'
        ]);

        $memoryBank3 = MemoryBank::factory()->create([
            'user_id' => $this->user->id,
            'book' => 'Philippians',
            'chapter' => 4,
            'verses' => '[[13, 13]]'
        ]);

        // Set up quiz session data
        session([
            'dailyQuiz' => [
                'verses' => [$memoryBank1, $memoryBank2, $memoryBank3],
                'currentIndex' => 0,
                'totalQuestions' => 3,
                'startedAt' => now()
            ]
        ]);

        $this->actingAs($this->user);

        $response = $this->get('/daily-quiz?quiz_mode=1');
        
        $response->assertStatus(200)
            ->assertSee('Quiz in Progress')
            ->assertSee('Verse 1 of 3')
            ->assertSeeHtml('quizMode: true')
            ->assertSeeHtml('@click="nextQuizVerse()"')
            ->assertSee('Next Verse')
            ->assertSee('Exit Quiz');
    }

    /** @test */
    public function regular_memorization_mode_shows_standard_buttons()
    {
        // Set verse selection for regular memorization
        session()->put('verseSelection', [
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 16]]
        ]);
        
        session()->put('fetchedVerseText', [
            'data' => [[
                'content' => 'For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.'
            ]]
        ]);

        $this->actingAs($this->user);

        $response = $this->get('/memorization-tool/display');
        
        $response->assertStatus(200)
            ->assertSeeHtml('quizMode: false')
            ->assertSee('Do Another')
            ->assertSee('Back Home')
            ->assertDontSee('Next Verse')
            ->assertDontSee('Exit Quiz');
    }

    /** @test */
    public function next_quiz_verse_endpoint_processes_completion_correctly()
    {
        // Set up a quiz session with multiple verses
        $verses = MemoryBank::factory()
            ->for($this->user)
            ->count(2)
            ->create();

        session()->put('dailyQuiz', [
            'type' => 'random',
            'numberOfQuestions' => 2,
            'verses' => $verses->toArray(),
            'currentIndex' => 0,
            'startTime' => now(),
            'difficulty' => 'easy',
            'results' => []
        ]);

        $this->actingAs($this->user);

        $response = $this->postJson('/daily-quiz/next', [
            'score' => 95,
            'difficulty' => 'easy',
            'user_text' => 'Sample user typed text'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'quiz_complete' => false,
                'redirect_url' => route('daily-quiz') . '?quiz_mode=1'
            ]);

        // Check that the quiz session was updated
        $quizData = session('dailyQuiz');
        $this->assertEquals(1, $quizData['currentIndex']);
        $this->assertCount(1, $quizData['results']);
        $this->assertEquals(95, $quizData['results'][0]['score']);
    }

    /** @test */
    public function quiz_completion_redirects_to_results()
    {
        // Set up a quiz session on the last verse
        $verses = MemoryBank::factory()
            ->for($this->user)
            ->count(2)
            ->create();

        session()->put('dailyQuiz', [
            'type' => 'random',
            'numberOfQuestions' => 2,
            'verses' => $verses->toArray(),
            'currentIndex' => 1, // Last verse (0-indexed)
            'startTime' => now(),
            'difficulty' => 'easy',
            'results' => [
                [
                    'verse' => $verses->first()->toArray(),
                    'score' => 90,
                    'difficulty' => 'easy',
                    'user_text' => 'First verse text',
                    'completed_at' => now()->toISOString()
                ]
            ]
        ]);

        $this->actingAs($this->user);

        $response = $this->postJson('/daily-quiz/next', [
            'score' => 88,
            'difficulty' => 'easy',
            'user_text' => 'Second verse text'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'quiz_complete' => true,
                'redirect_url' => route('daily-quiz.results')
            ]);

        // Check that the final result was recorded
        $quizData = session('dailyQuiz');
        $this->assertEquals(2, $quizData['currentIndex']);
        $this->assertCount(2, $quizData['results']);
    }

    /** @test */
    public function difficulty_selector_is_hidden_during_quiz()
    {
        // Create memory bank entries for the quiz
        $memoryBank1 = MemoryBank::factory()->create([
            'user_id' => $this->user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => '[[16, 17]]'
        ]);

        // Set up quiz session data
        session([
            'dailyQuiz' => [
                'verses' => [$memoryBank1],
                'currentIndex' => 0,
                'totalQuestions' => 1,
                'difficulty' => 'easy',
                'results' => [],
                'startedAt' => now()
            ]
        ]);

        $this->actingAs($this->user);

        $response = $this->get('/daily-quiz?quiz_mode=1');
        
        $response->assertStatus(200);
        
        // Check that the difficulty selector UI elements are hidden
        // These should not appear when in quiz mode
        $response->assertDontSee('id="easy"')
            ->assertDontSee('id="normal"')
            ->assertDontSee('id="strict"')
            ->assertDontSee('for="easy"')
            ->assertDontSee('for="normal"')
            ->assertDontSee('for="strict"');
        
        // Check that quizMode is set to true in the JavaScript
        $response->assertSee('quizMode: true');
    }
}