<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\MemorizeLater;
use Livewire\Livewire;
use App\Livewire\MemorizeLater as MemorizeLaterComponent;
use App\Livewire\MemorizeLaterList;

class MemorizeLaterTest extends TestCase
{
    use RefreshDatabase;

    public function test_memorize_later_component_renders()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->get('/')
            ->assertStatus(200)
            ->assertSeeLivewire('memorize-later');
    }

    public function test_user_can_save_single_verse()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLaterComponent::class)
            ->set('verse', 'John 3:16')
            ->set('note', 'This is a test note')
            ->call('saveVerse')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Verse saved successfully!')
            ->assertSet('verse', '')
            ->assertSet('note', '');

        $record = MemorizeLater::where('user_id', $user->id)->first();
        $this->assertNotNull($record);
        $this->assertEquals('John', $record->book);
        $this->assertEquals(3, $record->chapter);
        $this->assertEquals([16], $record->verses);
        $this->assertEquals('This is a test note', $record->note);
    }

    public function test_user_can_save_verse_range()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLaterComponent::class)
            ->set('verse', 'Psalms 23:1-6')
            ->call('saveVerse')
            ->assertHasNoErrors();

        $record = MemorizeLater::where('user_id', $user->id)->first();
        $this->assertNotNull($record);
        $this->assertEquals('Psalms', $record->book);
        $this->assertEquals(23, $record->chapter);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $record->verses);
    }

    public function test_user_can_save_verse_with_number_in_book_name()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLaterComponent::class)
            ->set('verse', '1 John 4:7')
            ->call('saveVerse')
            ->assertHasNoErrors();

        $record = MemorizeLater::where('user_id', $user->id)->first();
        $this->assertNotNull($record);
        $this->assertEquals('1 John', $record->book);
        $this->assertEquals(4, $record->chapter);
        $this->assertEquals([7], $record->verses);
    }

    public function test_user_can_save_verse_without_note()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLaterComponent::class)
            ->set('verse', 'Romans 8:28')
            ->set('note', '')
            ->call('saveVerse')
            ->assertHasNoErrors();

        $record = MemorizeLater::where('user_id', $user->id)->first();
        $this->assertNotNull($record);
        $this->assertEquals('Romans', $record->book);
        $this->assertEquals(8, $record->chapter);
        $this->assertEquals([28], $record->verses);
        $this->assertNull($record->note);
    }

    public function test_verse_is_required()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLaterComponent::class)
            ->set('verse', '')
            ->call('saveVerse')
            ->assertHasErrors(['verse' => 'required']);
    }

    public function test_invalid_verse_format_shows_error()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLaterComponent::class)
            ->set('verse', 'invalid format')
            ->call('saveVerse')
            ->assertHasErrors(['verse']);
    }

    public function test_verse_with_invalid_range_shows_error()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLaterComponent::class)
            ->set('verse', 'John 3:16-10') // End verse before start verse
            ->call('saveVerse')
            ->assertHasErrors(['verse']);
    }

    public function test_note_can_be_long()
    {
        $user = User::factory()->create();
        $longNote = str_repeat('This is a long note. ', 40); // About 800 chars

        Livewire::actingAs($user)
            ->test(MemorizeLaterComponent::class)
            ->set('verse', 'John 3:16')
            ->set('note', $longNote)
            ->call('saveVerse')
            ->assertHasNoErrors();
    }

    public function test_note_too_long_shows_error()
    {
        $user = User::factory()->create();
        $tooLongNote = str_repeat('X', 1001); // Over 1000 char limit

        Livewire::actingAs($user)
            ->test(MemorizeLaterComponent::class)
            ->set('verse', 'John 3:16')
            ->set('note', $tooLongNote)
            ->call('saveVerse')
            ->assertHasErrors(['note' => 'max']);
    }

    public function test_unauthenticated_user_cannot_save_verse()
    {
        Livewire::test(MemorizeLaterComponent::class)
            ->set('verse', 'John 3:16')
            ->call('saveVerse')
            ->assertHasErrors(['verse']);
    }

    public function test_toggle_expanded_state()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLaterComponent::class)
            ->assertSet('isExpanded', false)
            ->call('toggleExpanded')
            ->assertSet('isExpanded', true)
            ->call('toggleExpanded')
            ->assertSet('isExpanded', false);
    }

    public function test_toggle_expanded_clears_form_when_collapsed()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MemorizeLaterComponent::class)
            ->set('verse', 'John 3:16')
            ->set('note', 'Test note')
            ->set('successMessage', 'Success!')
            ->call('toggleExpanded') // Expand
            ->call('toggleExpanded') // Collapse
            ->assertSet('verse', '')
            ->assertSet('note', '')
            ->assertSet('successMessage', '');
    }

    public function test_model_relationships()
    {
        $user = User::factory()->create();
        
        $memorizeLater = MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'note' => 'Test note',
            'added_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $memorizeLater->user);
        $this->assertEquals($user->id, $memorizeLater->user->id);
    }

    public function test_verses_are_cast_to_array()
    {
        $user = User::factory()->create();
        
        $memorizeLater = MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16, 17, 18],
            'note' => 'Test note',
            'added_at' => now(),
        ]);

        $this->assertIsArray($memorizeLater->verses);
        $this->assertEquals([16, 17, 18], $memorizeLater->verses);
    }

    public function test_added_at_is_cast_to_datetime()
    {
        $user = User::factory()->create();
        $now = now();
        
        $memorizeLater = MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'added_at' => $now,
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $memorizeLater->added_at);
    }

    public function test_memorize_later_list_renders()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->get('/')
            ->assertStatus(200)
            ->assertSeeLivewire('memorize-later-list');
    }

    public function test_memorize_later_list_shows_user_verses()
    {
        $user = User::factory()->create();
        
        // Create a verse for this user
        MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'note' => 'Great verse!',
            'added_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(MemorizeLaterList::class)
            ->assertSee('John 3:16')
            ->assertSee('Note - ')
            ->assertSee('Great verse!');
    }

    public function test_remove_verse_functionality_has_been_removed()
    {
        // As per UX improvements (todo.md line 7-9), the removeVerse functionality
        // has been removed to improve user experience
        $component = new MemorizeLaterList();
        
        $this->assertFalse(method_exists($component, 'removeVerse'), 
            'The removeVerse method should be removed from the MemorizeLaterList component');
    }

    public function test_user_can_select_verse_for_memorization()
    {
        $user = User::factory()->create();
        
        $verse = MemorizeLater::create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16, 17],
            'added_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(MemorizeLaterList::class)
            ->call('selectVerse', $verse->id)
            ->assertRedirect('/memorization-tool/fetch-verse');

        // Check that verse selection was stored in session
        $this->assertEquals([
            'book' => 'John',
            'chapter' => 3,
            'verseRanges' => [[16, 17]], // Consecutive verses should be grouped
        ], session('verseSelection'));
    }
}
