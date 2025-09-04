<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Livewire\Livewire;
use App\Livewire\VersePicker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VersePickerSuggestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_verse_picker_shows_clickable_suggestion_for_typos(): void
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(VersePicker::class)
            ->set('input', 'Jogn 3:16')  // Typo: "Jogn" instead of "John"
            ->assertSet('errorMessage', "Unrecognized book 'Jogn'. Did you mean 'John'?")
            ->assertSet('suggestedBook', 'John')
            ->assertSee('Did you mean')
            ->assertSee('John');
    }

    public function test_clicking_suggestion_corrects_the_input(): void
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(VersePicker::class)
            ->set('input', 'Jogn 3:16')  // Typo: "Jogn" instead of "John"
            ->assertSet('suggestedBook', 'John')
            ->call('applySuggestion')
            ->assertSet('input', 'John 3:16')  // Should be corrected
            ->assertSet('errorMessage', '')     // Error should be cleared
            ->assertSet('suggestedBook', '')    // Suggestion should be cleared
            ->assertSet('book', 'John')         // Should parse correctly now
            ->assertSet('chapter', '3');
    }

    public function test_verse_picker_handles_non_suggestion_errors_normally(): void
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(VersePicker::class)
            ->set('input', 'Abcdefghijk 3:16')  // Invalid book with no close match
            ->assertSet('suggestedBook', '')  // No suggestion for this error
            ->assertSee("Unrecognized book 'Abcdefghijk'.");
    }
}
