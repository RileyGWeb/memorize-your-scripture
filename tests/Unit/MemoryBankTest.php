<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\MemoryBank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemoryBankTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_memory_bank_entry()
    {
        $user = User::factory()->create();
        
        $memoryBank = MemoryBank::create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'difficulty' => 'easy',
            'accuracy_score' => 95.5,
            'memorized_at' => now(),
            'user_text' => 'For God so loved the world...',
            'bible_translation' => 'NIV',
        ]);

        $this->assertInstanceOf(MemoryBank::class, $memoryBank);
        $this->assertEquals($user->id, $memoryBank->user_id);
        $this->assertEquals('John', $memoryBank->book);
        $this->assertEquals(3, $memoryBank->chapter);
        $this->assertEquals([16], $memoryBank->verses);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $memoryBank = MemoryBank::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $memoryBank->user);
        $this->assertEquals($user->id, $memoryBank->user->id);
    }

    /** @test */
    public function verses_are_cast_to_array()
    {
        $user = User::factory()->create();
        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id,
            'verses' => [1, 2, 3]
        ]);

        $this->assertIsArray($memoryBank->verses);
        $this->assertEquals([1, 2, 3], $memoryBank->verses);
    }

    /** @test */
    public function memorized_at_is_cast_to_datetime()
    {
        $user = User::factory()->create();
        $date = now();
        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id,
            'memorized_at' => $date
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $memoryBank->memorized_at);
        // Use format comparison instead of equalTo due to microsecond differences
        $this->assertEquals($date->format('Y-m-d H:i:s'), $memoryBank->memorized_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_handle_single_verse()
    {
        $user = User::factory()->create();
        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id,
            'verses' => [16]
        ]);

        $this->assertEquals([16], $memoryBank->verses);
    }

    /** @test */
    public function it_can_handle_multiple_verses()
    {
        $user = User::factory()->create();
        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id,
            'verses' => [16, 17, 18]
        ]);

        $this->assertEquals([16, 17, 18], $memoryBank->verses);
    }

    /** @test */
    public function it_can_store_different_difficulties()
    {
        $user = User::factory()->create();
        
        $difficulties = ['easy', 'normal', 'strict'];
        
        foreach ($difficulties as $difficulty) {
            $memoryBank = MemoryBank::factory()->create([
                'user_id' => $user->id,
                'difficulty' => $difficulty
            ]);
            
            $this->assertEquals($difficulty, $memoryBank->difficulty);
        }
    }

    /** @test */
    public function it_can_store_different_bible_translations()
    {
        $user = User::factory()->create();
        
        $translations = ['NIV', 'ESV', 'KJV', 'NASB'];
        
        foreach ($translations as $translation) {
            $memoryBank = MemoryBank::factory()->create([
                'user_id' => $user->id,
                'bible_translation' => $translation
            ]);
            
            $this->assertEquals($translation, $memoryBank->bible_translation);
        }
    }

    /** @test */
    public function it_stores_accuracy_scores_correctly()
    {
        $user = User::factory()->create();
        
        $scores = [80.0, 95.5, 100.0, 78.9];
        
        foreach ($scores as $score) {
            $memoryBank = MemoryBank::factory()->create([
                'user_id' => $user->id,
                'accuracy_score' => $score
            ]);
            
            $this->assertEquals($score, $memoryBank->accuracy_score);
        }
    }

    /** @test */
    public function it_uses_auditable_trait()
    {
        $user = User::factory()->create();
        
        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id,
        ]);

        // Check that the Auditable trait is being used
        $this->assertContains('App\Traits\Auditable', class_uses_recursive(MemoryBank::class));
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $memoryBank = new MemoryBank();
        
        $expectedFillable = [
            'user_id',
            'book',
            'chapter',
            'verses',
            'difficulty',
            'accuracy_score',
            'memorized_at',
            'user_text',
            'bible_translation',
        ];

        $this->assertEquals($expectedFillable, $memoryBank->getFillable());
    }

    /** @test */
    public function it_has_correct_table_name()
    {
        $memoryBank = new MemoryBank();
        
        $this->assertEquals('memory_bank', $memoryBank->getTable());
    }

    /** @test */
    public function it_can_query_by_book()
    {
        $user = User::factory()->create();
        
        // Clear any existing data to ensure clean test
        MemoryBank::truncate();
        
        MemoryBank::factory()->create([
            'user_id' => $user->id,
            'book' => 'John'
        ]);
        
        MemoryBank::factory()->create([
            'user_id' => $user->id,
            'book' => 'Romans'
        ]);

        $johnEntries = MemoryBank::where('book', 'John')->get();
        $romansEntries = MemoryBank::where('book', 'Romans')->get();

        $this->assertCount(1, $johnEntries);
        $this->assertCount(1, $romansEntries);
        $this->assertEquals('John', $johnEntries->first()->book);
        $this->assertEquals('Romans', $romansEntries->first()->book);
    }

    /** @test */
    public function it_can_query_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        MemoryBank::factory()->count(3)->create(['user_id' => $user1->id]);
        MemoryBank::factory()->count(2)->create(['user_id' => $user2->id]);

        $user1Entries = MemoryBank::where('user_id', $user1->id)->get();
        $user2Entries = MemoryBank::where('user_id', $user2->id)->get();

        $this->assertCount(3, $user1Entries);
        $this->assertCount(2, $user2Entries);
    }

    /** @test */
    public function it_can_query_by_difficulty()
    {
        $user = User::factory()->create();
        
        // Clear any existing data to ensure clean test
        MemoryBank::truncate();
        
        MemoryBank::factory()->create([
            'user_id' => $user->id,
            'difficulty' => 'easy'
        ]);
        
        MemoryBank::factory()->create([
            'user_id' => $user->id,
            'difficulty' => 'strict'
        ]);

        $easyEntries = MemoryBank::where('difficulty', 'easy')->get();
        $strictEntries = MemoryBank::where('difficulty', 'strict')->get();

        $this->assertCount(1, $easyEntries);
        $this->assertCount(1, $strictEntries);
    }
}
