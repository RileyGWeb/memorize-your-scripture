<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use Livewire\Livewire;
use App\Livewire\DailyQuiz;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DailyQuizInitializationTest extends TestCase
{
    use RefreshDatabase;

    public function test_number_of_questions_initializes_to_memory_bank_count_when_less_than_10(): void
    {
        $user = User::factory()->create();
        
        // Create 3 memory bank entries
        MemoryBank::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $component = Livewire::test(DailyQuiz::class);
        
        // Should initialize to 3 (the number of memorized verses)
        $component->assertSet('numberOfQuestions', 3);
    }

    public function test_number_of_questions_initializes_to_10_when_memory_bank_count_exceeds_10(): void
    {
        $user = User::factory()->create();
        
        // Create 15 memory bank entries
        MemoryBank::factory()->count(15)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $component = Livewire::test(DailyQuiz::class);
        
        // Should initialize to 10 (max default)
        $component->assertSet('numberOfQuestions', 10);
    }

    public function test_number_of_questions_initializes_to_1_when_no_verses_memorized(): void
    {
        $user = User::factory()->create();
        // No memory bank entries created

        $this->actingAs($user);

        $component = Livewire::test(DailyQuiz::class);
        
        // Should initialize to 1 (minimum)
        $component->assertSet('numberOfQuestions', 1);
    }

    public function test_cannot_increase_questions_beyond_memorized_verses(): void
    {
        $user = User::factory()->create();
        
        // Create 5 memory bank entries
        MemoryBank::factory()->count(5)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $component = Livewire::test(DailyQuiz::class)
            ->assertSet('numberOfQuestions', 5)
            ->call('increaseNumber')
            ->assertSet('numberOfQuestions', 5); // Should not increase beyond 5
    }
}
