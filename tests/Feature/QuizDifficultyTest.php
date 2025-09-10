<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\QuizTaker;

class QuizDifficultyTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_difficulty_defaults_to_easy()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create memory bank entries
        MemoryBank::factory()->count(3)->create(['user_id' => $user->id]);

        // Set up quiz session
        session(['dailyQuiz' => [
            'type' => 'random',
            'verses' => [
                ['book' => 'John', 'chapter' => 3, 'verses' => [[1, 1]]],
            ],
            'currentIndex' => 0,
            'startTime' => now(),
        ]]);

        $component = Livewire::test(QuizTaker::class);
        
        $this->assertEquals('easy', $component->get('difficulty'));
        $this->assertEquals(80, $component->instance()->getRequiredAccuracy());
    }

    public function test_quiz_difficulty_can_be_changed()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create memory bank entries
        MemoryBank::factory()->count(3)->create(['user_id' => $user->id]);

        // Set up quiz session
        session(['dailyQuiz' => [
            'type' => 'random',
            'verses' => [
                ['book' => 'John', 'chapter' => 3, 'verses' => [[1, 1]]],
            ],
            'currentIndex' => 0,
            'startTime' => now(),
        ]]);

        $component = Livewire::test(QuizTaker::class);
        
        // Test changing to normal
        $component->set('difficulty', 'normal');
        $this->assertEquals('normal', $component->get('difficulty'));
        $this->assertEquals(95, $component->instance()->getRequiredAccuracy());

        // Test changing to strict
        $component->set('difficulty', 'strict');
        $this->assertEquals('strict', $component->get('difficulty'));
        $this->assertEquals(100, $component->instance()->getRequiredAccuracy());
    }

    public function test_quiz_scoring_respects_difficulty_thresholds()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create memory bank entries
        MemoryBank::factory()->count(3)->create(['user_id' => $user->id]);

        // Set up quiz session
        session(['dailyQuiz' => [
            'type' => 'random',
            'verses' => [
                ['book' => 'John', 'chapter' => 3, 'verses' => [[1, 1]]],
                ['book' => 'John', 'chapter' => 3, 'verses' => [[2, 2]]],
            ],
            'currentIndex' => 0,
            'startTime' => now(),
        ]]);

        $component = Livewire::test(QuizTaker::class);

        // Test easy difficulty (80% threshold)
        $component->set('difficulty', 'easy');
        $component->set('userInput', 'This is a partial match');
        $component->set('actualVerseText', 'This is a complete verse text');
        
        // Mock accuracy calculation to return 85% (should pass easy)
        $accuracy = $component->instance()->calculateAccuracy('This is a partial match', 'This is a complete verse text');
        $this->assertGreaterThan(50, $accuracy); // Should have some similarity

        // Test strict difficulty (100% threshold) 
        $component->set('difficulty', 'strict');
        // Same text won't pass strict mode unless perfect
        $requiredAccuracy = $component->instance()->getRequiredAccuracy();
        $this->assertEquals(100, $requiredAccuracy);
    }

    public function test_quiz_results_include_difficulty_information()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create memory bank entries
        MemoryBank::factory()->count(3)->create(['user_id' => $user->id]);

        // Set up quiz session with one verse
        session(['dailyQuiz' => [
            'type' => 'random',
            'verses' => [
                ['book' => 'John', 'chapter' => 3, 'verses' => [[1, 1]]],
            ],
            'currentIndex' => 0,
            'startTime' => now(),
        ]]);

        $component = Livewire::test(QuizTaker::class);
        
        // Set difficulty and simulate answering
        $component->set('difficulty', 'normal');
        $component->set('userInput', 'Test answer');
        $component->set('actualVerseText', 'Test answer with slight difference');
        
        $component->call('nextQuestion');
        
        $results = $component->get('results');
        $this->assertNotEmpty($results);
        $this->assertEquals('normal', $results[0]['difficulty']);
        $this->assertEquals(95, $results[0]['requiredAccuracy']);
        $this->assertArrayHasKey('passed', $results[0]);
    }

    public function test_quiz_completion_includes_difficulty_stats_in_audit_log()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create memory bank entries
        MemoryBank::factory()->count(3)->create(['user_id' => $user->id]);

        // Set up quiz session with one verse
        session(['dailyQuiz' => [
            'type' => 'random',
            'verses' => [
                ['book' => 'John', 'chapter' => 3, 'verses' => [[1, 1]]],
            ],
            'currentIndex' => 0,
            'startTime' => now(),
        ]]);

        $component = Livewire::test(QuizTaker::class);
        
        // Complete the quiz
        $component->set('difficulty', 'normal');
        $component->set('userInput', 'Test answer');
        $component->set('actualVerseText', 'Test answer');
        $component->call('nextQuestion');

        // Check that quiz completion was logged
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'quiz_completed',
        ]);

        // Check that the audit log includes difficulty stats
        $auditLog = \App\Models\AuditLog::where('user_id', $user->id)
            ->where('action', 'quiz_completed')
            ->latest()
            ->first();

        $newValues = $auditLog->new_values;
        $this->assertArrayHasKey('difficulty_stats', $newValues);
    }
}
