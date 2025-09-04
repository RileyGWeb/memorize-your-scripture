<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\MemoryBank;
use App\Livewire\DailyQuiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DailyQuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_quiz_component_renders()
    {
        $user = User::factory()->create();
        
        // Create some memory bank entries so quiz options show
        MemoryBank::factory()->count(5)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(DailyQuiz::class)
            ->assertSee('Daily Quiz!')
            ->assertSee('Daily juice to keep those verses in your brain')
            ->assertSee('randoms')
            ->assertSee('most recent')
            ->assertSee('longest')
            ->assertSee('shortest')
            ->assertSee('Total verses memorized: 5');
    }

    public function test_user_can_change_number_of_questions()
    {
        $user = User::factory()->create();
        
        // Create enough memory bank entries to allow increasing
        MemoryBank::factory()->count(15)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(DailyQuiz::class)
            ->assertSet('numberOfQuestions', 10)
            ->call('increaseNumber')
            ->assertSet('numberOfQuestions', 11)
            ->call('decreaseNumber')
            ->assertSet('numberOfQuestions', 10)
            ->call('decreaseNumber')
            ->assertSet('numberOfQuestions', 9);
    }

    public function test_number_of_questions_has_limits()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(DailyQuiz::class)
            ->set('numberOfQuestions', 1)
            ->call('decreaseNumber')
            ->assertSet('numberOfQuestions', 1); // Should not go below 1

        $component->set('numberOfQuestions', 50)
            ->call('increaseNumber')
            ->assertSet('numberOfQuestions', 50); // Should not go above 50
    }

    public function test_user_with_no_memory_bank_sees_message()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(DailyQuiz::class)
            ->assertSee("haven't memorized any verses", false)
            ->assertSee('Start memorizing verses');
    }

    public function test_user_can_start_random_quiz()
    {
        $user = User::factory()->create();
        
        // Create some memory bank entries
        MemoryBank::factory()->forUser($user)->count(5)->create();

        $this->actingAs($user);

        Livewire::test(DailyQuiz::class)
            ->call('startQuiz', 'random')
            ->assertRedirect(route('daily-quiz'));

        // Check that session has quiz data
        $this->assertNotNull(session('dailyQuiz'));
        $this->assertEquals('random', session('dailyQuiz')['type']);
        $this->assertEquals(10, session('dailyQuiz')['numberOfQuestions']);
    }

    public function test_user_can_start_recent_quiz()
    {
        $user = User::factory()->create();
        
        // Create some memory bank entries with memorized_at dates
        MemoryBank::factory()->forUser($user)->count(5)->create([
            'memorized_at' => now()->subDays(1),
        ]);

        $this->actingAs($user);

        Livewire::test(DailyQuiz::class)
            ->call('startQuiz', 'recent')
            ->assertRedirect(route('daily-quiz'));

        $quizData = session('dailyQuiz');
        $this->assertEquals('recent', $quizData['type']);
    }

    public function test_unauthenticated_user_cannot_start_quiz()
    {
        // Test that unauthenticated users can't start quiz (will show login required)
        $component = Livewire::test(DailyQuiz::class)
            ->call('startQuiz', 'random');
        
        // The component should not redirect since user is not authenticated
        $component->assertNoRedirect();
    }

    public function test_quiz_with_no_verses_shows_error()
    {
        $user = User::factory()->create();
        // No memory bank entries created

        $this->actingAs($user);

        $component = Livewire::test(DailyQuiz::class)
            ->call('startQuiz', 'random');
            
        // Should not redirect since no verses available
        $component->assertNoRedirect();
    }

    public function test_memory_bank_count_is_accurate()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(DailyQuiz::class);
        $this->assertEquals(0, $component->instance()->getMemoryBankCount());

        MemoryBank::factory()->forUser($user)->count(3)->create();

        // Create a new component instance to get fresh data
        $freshComponent = Livewire::test(DailyQuiz::class);
        $this->assertEquals(3, $freshComponent->instance()->getMemoryBankCount());
    }

    public function test_calculate_verse_length_correctly()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(DailyQuiz::class);

        // Create a verse with single verse
        $singleVerse = MemoryBank::factory()->forUser($user)->create([
            'verses' => [[1, 1]]
        ]);

        // Create a verse with range
        $rangeVerse = MemoryBank::factory()->forUser($user)->create([
            'verses' => [[1, 3]]
        ]);

        $this->assertEquals(1, $component->instance()->calculateVerseLength($singleVerse));
        $this->assertEquals(3, $component->instance()->calculateVerseLength($rangeVerse));
    }
}
