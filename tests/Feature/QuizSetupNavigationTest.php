<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuizSetupNavigationTest extends TestCase
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
            ->count(5)
            ->create();
    }

    /** @test */
    public function can_navigate_to_next_quiz_type()
    {
        // Set up quiz session starting with 'random'
        session()->put('quizSetup', [
            'type' => 'random',
            'numberOfQuestions' => 3,
            'verses' => []
        ]);

        $this->actingAs($this->user);

        Livewire::test('quiz-setup')
            ->assertSet('quizType', 'random')
            ->assertSet('quizTypeLabel', 'Random verses')
            ->call('nextQuizType')
            ->assertSet('quizType', 'recent')
            ->assertSet('quizTypeLabel', 'Most recent verses');
    }

    /** @test */
    public function can_navigate_to_previous_quiz_type()
    {
        // Set up quiz session starting with 'recent'
        session()->put('quizSetup', [
            'type' => 'recent',
            'numberOfQuestions' => 3,
            'verses' => []
        ]);

        $this->actingAs($this->user);

        Livewire::test('quiz-setup')
            ->assertSet('quizType', 'recent')
            ->assertSet('quizTypeLabel', 'Most recent verses')
            ->call('previousQuizType')
            ->assertSet('quizType', 'random')
            ->assertSet('quizTypeLabel', 'Random verses');
    }

    /** @test */
    public function navigation_wraps_around_at_end_of_quiz_types()
    {
        // Test forward wrapping (all -> random)
        session()->put('quizSetup', [
            'type' => 'all',
            'numberOfQuestions' => 3,
            'verses' => []
        ]);

        $this->actingAs($this->user);

        Livewire::test('quiz-setup')
            ->assertSet('quizType', 'all')
            ->call('nextQuizType')
            ->assertSet('quizType', 'random');
    }

    /** @test */
    public function navigation_wraps_around_at_beginning_of_quiz_types()
    {
        // Test backward wrapping (random -> all)
        session()->put('quizSetup', [
            'type' => 'random',
            'numberOfQuestions' => 3,
            'verses' => []
        ]);

        $this->actingAs($this->user);

        Livewire::test('quiz-setup')
            ->assertSet('quizType', 'random')
            ->call('previousQuizType')
            ->assertSet('quizType', 'all');
    }

    /** @test */
    public function navigation_updates_session_data()
    {
        session()->put('quizSetup', [
            'type' => 'random',
            'numberOfQuestions' => 3,
            'verses' => []
        ]);

        $this->actingAs($this->user);

        Livewire::test('quiz-setup')
            ->call('nextQuizType');

        // Check that session was updated
        $this->assertEquals('recent', session('quizSetup.type'));
    }

    /** @test */
    public function quiz_setup_page_shows_navigation_arrows()
    {
        session()->put('quizSetup', [
            'type' => 'longest',
            'numberOfQuestions' => 3,
            'verses' => []
        ]);

        $this->actingAs($this->user);

        $response = $this->get('/quiz/setup');
        
        $response->assertStatus(200)
            ->assertSee('Longest verses') // Current quiz type label
            ->assertSeeHtml('wire:click="previousQuizType"') // Left arrow
            ->assertSeeHtml('wire:click="nextQuizType"'); // Right arrow
    }
}