<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuizRouteTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function get_quizzed_button_points_to_quiz_route()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('href="/quiz"', false);
        $response->assertSee('Get quizzed');
    }
    
    /** @test */
    public function quiz_route_shows_daily_quiz_when_no_memory_bank()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/quiz');
        
        $response->assertStatus(200);
        $response->assertSee('You need to memorize some verses first!');
    }
    
    /** @test */
    public function quiz_route_shows_quiz_selection_when_memory_bank_exists()
    {
        $user = User::factory()->create();
        MemoryBank::factory()->forUser($user)->create([
            'book' => 'John',
            'chapter' => 3,
            'verses' => [[16]],
        ]);
        
        $response = $this->actingAs($user)->get('/quiz');
        
        $response->assertStatus(200);
        $response->assertSee('Daily Quiz!');
        $response->assertSee('Total verses:'); // Updated to match new UI text
    }
}
