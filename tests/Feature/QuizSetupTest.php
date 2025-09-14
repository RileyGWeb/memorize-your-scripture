<?php

namespace Tests\Feature;

use App\Livewire\QuizSetup;
use App\Models\User;
use App\Models\MemoryBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuizSetupTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test quiz setup component mounts correctly with session data.
     *
     * @test
     */
    public function quiz_setup_mounts_correctly_with_session_data()
    {
        session()->put('quizSetup', [
            'numberOfQuestions' => 10,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('numberOfQuestions', 10)
            ->assertSet('difficulty', 'easy')
            ->assertSet('quizType', 'random')
            ->assertSet('quizTypeLabel', 'Random verses');
    }

    /**
     * Test quiz setup redirects when no session data exists.
     *
     * @test
     */
    public function quiz_setup_redirects_when_no_session_data_exists()
    {
        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertRedirect('/daily-quiz')
            ->assertSessionHas('error');
    }

    /**
     * Test increase number functionality.
     *
     * @test
     */
    public function can_increase_number_of_questions()
    {
        // Create some memory bank entries
        MemoryBank::factory()->count(20)->create(['user_id' => $this->user->id]);

        session()->put('quizSetup', [
            'numberOfQuestions' => 5,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('numberOfQuestions', 5)
            ->call('increaseNumber')
            ->assertSet('numberOfQuestions', 6);
    }

    /**
     * Test increase number by 5 functionality.
     *
     * @test
     */
    public function can_increase_number_by_5()
    {
        MemoryBank::factory()->count(20)->create(['user_id' => $this->user->id]);

        session()->put('quizSetup', [
            'numberOfQuestions' => 5,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('numberOfQuestions', 5)
            ->call('increaseNumberBy5')
            ->assertSet('numberOfQuestions', 10);
    }

    /**
     * Test decrease number functionality.
     *
     * @test
     */
    public function can_decrease_number_of_questions()
    {
        session()->put('quizSetup', [
            'numberOfQuestions' => 5,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('numberOfQuestions', 5)
            ->call('decreaseNumber')
            ->assertSet('numberOfQuestions', 4);
    }

    /**
     * Test decrease number by 5 functionality.
     *
     * @test
     */
    public function can_decrease_number_by_5()
    {
        session()->put('quizSetup', [
            'numberOfQuestions' => 10,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('numberOfQuestions', 10)
            ->call('decreaseNumberBy5')
            ->assertSet('numberOfQuestions', 5);
    }

    /**
     * Test number cannot go below 1.
     *
     * @test
     */
    public function number_cannot_go_below_1()
    {
        session()->put('quizSetup', [
            'numberOfQuestions' => 1,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('numberOfQuestions', 1)
            ->call('decreaseNumber')
            ->assertSet('numberOfQuestions', 1);
    }

    /**
     * Test decrease by 5 respects minimum of 1.
     *
     * @test
     */
    public function decrease_by_5_respects_minimum_of_1()
    {
        session()->put('quizSetup', [
            'numberOfQuestions' => 3,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('numberOfQuestions', 3)
            ->call('decreaseNumberBy5')
            ->assertSet('numberOfQuestions', 1);
    }

    /**
     * Test number cannot exceed available memory bank entries.
     *
     * @test
     */
    public function number_cannot_exceed_available_memory_bank_entries()
    {
        // Create only 3 memory bank entries
        MemoryBank::factory()->count(3)->create(['user_id' => $this->user->id]);

        session()->put('quizSetup', [
            'numberOfQuestions' => 3,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('numberOfQuestions', 3)
            ->call('increaseNumber')
            ->assertSet('numberOfQuestions', 3); // Should not increase
    }

    /**
     * Test number cannot exceed 50.
     *
     * @test
     */
    public function number_cannot_exceed_50()
    {
        // Create many memory bank entries
        MemoryBank::factory()->count(100)->create(['user_id' => $this->user->id]);

        session()->put('quizSetup', [
            'numberOfQuestions' => 50,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('numberOfQuestions', 50)
            ->call('increaseNumber')
            ->assertSet('numberOfQuestions', 50); // Should not increase
    }

    /**
     * Test start quiz functionality for authenticated user.
     *
     * @test
     */
    public function can_start_quiz_when_authenticated()
    {
        MemoryBank::factory()->count(5)->create(['user_id' => $this->user->id]);

        session()->put('quizSetup', [
            'numberOfQuestions' => 3,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->call('startQuiz')
            ->assertRedirect('/daily-quiz?quiz_mode=1');

        // Verify session data was created
        $this->assertNotNull(session('dailyQuiz'));
        $this->assertNull(session('quizSetup')); // Should be cleared
        
        $dailyQuiz = session('dailyQuiz');
        $this->assertEquals('random', $dailyQuiz['type']);
        $this->assertEquals(3, $dailyQuiz['numberOfQuestions']);
        $this->assertEquals(0, $dailyQuiz['currentIndex']);
        $this->assertEquals('easy', $dailyQuiz['difficulty']);
    }

    /**
     * Test start quiz fails for unauthenticated user.
     *
     * @test
     */
    public function start_quiz_fails_for_unauthenticated_user()
    {
        session()->put('quizSetup', [
            'numberOfQuestions' => 3,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::test(QuizSetup::class)
            ->call('startQuiz')
            ->assertHasErrors(); // Should show error about logging in
    }

    /**
     * Test quiz type labels are correct.
     *
     * @test
     */
    public function quiz_type_labels_are_correct()
    {
        $testCases = [
            'random' => 'Random verses',
            'recent' => 'Most recent verses',
            'longest' => 'Longest verses',
            'shortest' => 'Shortest verses',
            'all' => 'All verses',
            'unknown' => 'Random verses' // default case
        ];

        foreach ($testCases as $type => $expectedLabel) {
            session()->put('quizSetup', [
                'numberOfQuestions' => 5,
                'type' => $type,
                'verses' => []
            ]);

            Livewire::actingAs($this->user)
                ->test(QuizSetup::class)
                ->assertSet('quizTypeLabel', $expectedLabel);
        }
    }

    /**
     * Test difficulty can be changed.
     *
     * @test
     */
    public function difficulty_can_be_changed()
    {
        session()->put('quizSetup', [
            'numberOfQuestions' => 5,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('difficulty', 'easy')
            ->set('difficulty', 'normal')
            ->assertSet('difficulty', 'normal')
            ->set('difficulty', 'strict')
            ->assertSet('difficulty', 'strict');
    }

    /**
     * Test component renders without errors.
     *
     * @test
     */
    public function component_renders_without_errors()
    {
        session()->put('quizSetup', [
            'numberOfQuestions' => 5,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertViewIs('livewire.quiz-setup');
    }

    /**
     * Test increase by 5 respects memory bank count limit.
     *
     * @test
     */
    public function increase_by_5_respects_memory_bank_count_limit()
    {
        // Create only 8 memory bank entries
        MemoryBank::factory()->count(8)->create(['user_id' => $this->user->id]);

        session()->put('quizSetup', [
            'numberOfQuestions' => 6,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('numberOfQuestions', 6)
            ->call('increaseNumberBy5')
            ->assertSet('numberOfQuestions', 8); // Should cap at memory bank count
    }

    /**
     * Test increase by 5 respects 50 question limit.
     *
     * @test
     */
    public function increase_by_5_respects_50_question_limit()
    {
        MemoryBank::factory()->count(100)->create(['user_id' => $this->user->id]);

        session()->put('quizSetup', [
            'numberOfQuestions' => 48,
            'type' => 'random',
            'verses' => []
        ]);

        Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->assertSet('numberOfQuestions', 48)
            ->call('increaseNumberBy5')
            ->assertSet('numberOfQuestions', 50); // Should cap at 50
    }

    /**
     * Test that quiz setup updates session when questions change.
     *
     * @test
     */
    public function quiz_setup_updates_session_when_questions_change()
    {
        MemoryBank::factory()->count(10)->create(['user_id' => $this->user->id]);

        session()->put('quizSetup', [
            'numberOfQuestions' => 5,
            'type' => 'random',
            'verses' => []
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(QuizSetup::class)
            ->call('increaseNumber');

        // Verify session was updated
        $quizSetup = session('quizSetup');
        $this->assertEquals(6, $quizSetup['numberOfQuestions']);
    }
}
