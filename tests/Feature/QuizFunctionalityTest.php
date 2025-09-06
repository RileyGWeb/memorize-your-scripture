<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\MemoryBank;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\QuizTaker;

class QuizFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the Bible API to avoid external dependencies
        $this->app['config']->set('bible.api_key', 'test-api-key');
        $this->app['config']->set('bible.bible_id', 'test-bible-id');
    }

    public function test_quiz_displays_correct_progress_format()
    {
        $user = User::factory()->create();
        
        // Create memory bank verses for the user
        MemoryBank::factory()->count(5)->create([
            'user_id' => $user->id,
            'memorized_at' => now(),
        ]);

        // Set up quiz session data
        session()->put('dailyQuiz', [
            'type' => 'random',
            'numberOfQuestions' => 5,
            'verses' => MemoryBank::where('user_id', $user->id)->get()->toArray(),
            'currentIndex' => 0,
            'startTime' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(QuizTaker::class);

        // Check that progress shows "X of Y" format
        $component->assertSee('1 of 5');
        $component->assertSee('Daily Quiz');
    }

    public function test_quiz_tracks_score_correctly()
    {
        $user = User::factory()->create();
        
        $verses = MemoryBank::factory()->count(3)->create([
            'user_id' => $user->id,
            'memorized_at' => now(),
        ]);

        session()->put('dailyQuiz', [
            'type' => 'random',
            'numberOfQuestions' => 3,
            'verses' => $verses->toArray(),
            'currentIndex' => 0,
            'startTime' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(QuizTaker::class);

        // Initially no score shown
        $component->assertDontSee('Score:');

        // Simulate answering first question correctly
        $component->set('userInput', 'Test verse content')
            ->call('submitAnswer');

        // Mock the actual verse text for accuracy calculation
        $component->set('actualVerseText', 'Test verse content')
            ->call('nextQuestion');

        // Check score is displayed after first answer
        $component->assertSee('Score: 1/1');
        $component->assertSee('100%');
    }

    public function test_quiz_calculates_grade_correctly()
    {
        $user = User::factory()->create();
        
        $verses = MemoryBank::factory()->count(2)->create([
            'user_id' => $user->id,
            'memorized_at' => now(),
        ]);

        session()->put('dailyQuiz', [
            'type' => 'random',
            'numberOfQuestions' => 2,
            'verses' => $verses->toArray(),
            'currentIndex' => 0,
            'startTime' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(QuizTaker::class);

        // Answer both questions with high accuracy (should get A grade)
        $component->set('userInput', 'Perfect match')
            ->call('submitAnswer')
            ->set('actualVerseText', 'Perfect match')
            ->call('nextQuestion');

        $component->set('userInput', 'Another perfect match')
            ->call('submitAnswer')
            ->set('actualVerseText', 'Another perfect match');

        // Manually trigger completion since we can't reach end of array in test
        $component->call('completeQuiz');

        // Check quiz completion and grade
        $component->assertSee('Quiz Complete!');
        
        // Check session data has grade
        $results = session('quizResults');
        $this->assertNotNull($results);
        $this->assertEquals('A+', $results['grade']);
        $this->assertEquals(100, $results['percentageScore']);
    }

    public function test_quiz_creates_audit_log_entry()
    {
        $user = User::factory()->create();
        
        $verses = MemoryBank::factory()->count(2)->create([
            'user_id' => $user->id,
            'memorized_at' => now(),
        ]);

        session()->put('dailyQuiz', [
            'type' => 'random',
            'numberOfQuestions' => 2,
            'verses' => $verses->toArray(),
            'currentIndex' => 0,
            'startTime' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(QuizTaker::class);

        // Complete the quiz
        $component->set('userInput', 'Test answer')
            ->call('submitAnswer')
            ->set('actualVerseText', 'Test answer')
            ->call('nextQuestion');

        $component->set('userInput', 'Second test answer')
            ->call('submitAnswer')
            ->set('actualVerseText', 'Second test answer');

        $component->call('completeQuiz');

        // Check audit log was created
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'quiz_completed',
            'table_name' => 'quiz_sessions',
        ]);

        $auditLog = AuditLog::where('user_id', $user->id)
            ->where('action', 'quiz_completed')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertStringContainsString('random quiz', $auditLog->new_values['description']);
        $this->assertArrayHasKey('quiz_type', $auditLog->new_values);
        $this->assertArrayHasKey('grade', $auditLog->new_values);
        $this->assertArrayHasKey('percentage_score', $auditLog->new_values);
    }

    public function test_quiz_accuracy_calculation()
    {
        $user = User::factory()->create();
        
        $verses = MemoryBank::factory()->count(1)->create([
            'user_id' => $user->id,
            'memorized_at' => now(),
        ]);

        session()->put('dailyQuiz', [
            'type' => 'random',
            'numberOfQuestions' => 1,
            'verses' => $verses->toArray(),
            'currentIndex' => 0,
            'startTime' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(QuizTaker::class);

        // Test partial accuracy
        $component->set('userInput', 'For God so loved')
            ->call('submitAnswer')
            ->set('actualVerseText', 'For God so loved the world')
            ->call('nextQuestion');

        // The accuracy should be calculated but score should be 0 (less than 80%)
        $this->assertEquals(0, $component->get('score'));
        $this->assertEquals(1, $component->get('totalAnswered'));
    }

    public function test_quiz_percentage_display()
    {
        $user = User::factory()->create();
        
        $verses = MemoryBank::factory()->count(3)->create([
            'user_id' => $user->id,
            'memorized_at' => now(),
        ]);

        session()->put('dailyQuiz', [
            'type' => 'random',
            'numberOfQuestions' => 3,
            'verses' => $verses->toArray(),
            'currentIndex' => 0,
            'startTime' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(QuizTaker::class);

        // Answer first question correctly (should show 100%)
        $component->set('userInput', 'Perfect answer')
            ->call('submitAnswer')
            ->set('actualVerseText', 'Perfect answer')
            ->call('nextQuestion');

        $component->assertSee('Score: 1/1 (100%)', false);

        // Answer second question incorrectly (should show 50%)
        $component->set('userInput', 'Wrong answer')
            ->call('submitAnswer')
            ->set('actualVerseText', 'Correct answer is different')
            ->call('nextQuestion');

        $component->assertSee('Score: 1/2 (50%)', false);
    }

    public function test_grade_calculation_boundaries()
    {
        $component = new QuizTaker();
        
        // Test grade boundaries
        $this->assertEquals('A+', $component->calculateGrade(97));
        $this->assertEquals('A', $component->calculateGrade(95));
        $this->assertEquals('A-', $component->calculateGrade(90));
        $this->assertEquals('B+', $component->calculateGrade(87));
        $this->assertEquals('B', $component->calculateGrade(83));
        $this->assertEquals('B-', $component->calculateGrade(80));
        $this->assertEquals('C+', $component->calculateGrade(77));
        $this->assertEquals('C', $component->calculateGrade(73));
        $this->assertEquals('C-', $component->calculateGrade(70));
        $this->assertEquals('D+', $component->calculateGrade(67));
        $this->assertEquals('D', $component->calculateGrade(63));
        $this->assertEquals('D-', $component->calculateGrade(60));
        $this->assertEquals('F', $component->calculateGrade(50));
        $this->assertEquals('F', $component->calculateGrade(0));
    }

    public function test_quiz_completion_shows_detailed_results()
    {
        $user = User::factory()->create();
        
        $verses = MemoryBank::factory()->count(2)->create([
            'user_id' => $user->id,
            'memorized_at' => now(),
        ]);

        session()->put('dailyQuiz', [
            'type' => 'random',
            'numberOfQuestions' => 2,
            'verses' => $verses->toArray(),
            'currentIndex' => 0,
            'startTime' => now(),
        ]);

        $component = Livewire::actingAs($user)->test(QuizTaker::class);

        // Complete quiz with one correct answer
        $component->set('userInput', 'Correct')
            ->call('submitAnswer')
            ->set('actualVerseText', 'Correct')
            ->call('nextQuestion');

        $component->set('userInput', 'Wrong')
            ->call('submitAnswer')
            ->set('actualVerseText', 'Right')
            ->call('nextQuestion');

        // Should show completion screen with detailed results
        $component->assertSee('Quiz Complete!');
        $component->assertSee('2'); // Total questions
        $component->assertSee('1'); // Correct answers
        $component->assertSee('50%'); // Percentage
        $component->assertSee('Grade'); // Grade label
    }
}
