<?php

namespace Tests\Feature;

use App\Livewire\MemorizeLater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemorizeLaterVerseValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_verse_reference_shows_error()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLater::class)
            ->set('verse', 'John 3:999') // Invalid verse - John 3 only has 36 verses
            ->set('note', 'This verse does not exist')
            ->call('saveVerse')
            ->assertHasErrors(['verse' => 'This verse reference does not exist. Please check the book, chapter, and verse numbers.']);
    }

    public function test_valid_verse_reference_saves_successfully()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLater::class)
            ->set('verse', 'John 3:16') // Valid verse
            ->set('note', 'For God so loved the world')
            ->call('saveVerse')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Verse saved successfully!');
    }

    public function test_validation_gracefully_handles_api_errors()
    {
        // Test with no API key configured
        config(['services.bible.api_key' => null]);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLater::class)
            ->set('verse', 'John 3:16')
            ->set('note', 'Should save even without API validation')
            ->call('saveVerse')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Verse saved successfully!');
    }
}
