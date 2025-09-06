<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;

class UserVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_are_not_verified_by_default()
    {
        $user = User::factory()->create();
        
        $this->assertFalse($user->verified_user);
    }

    public function test_user_is_verified_when_memorizing_first_verse()
    {
        $user = User::factory()->create(['verified_user' => false]);
        $this->actingAs($user);

        // Make a request to memorize a verse
        $response = $this->postJson('/memorization-tool/save', [
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'difficulty' => 'easy', // Use valid enum value
            'accuracy_score' => 90.5,
            'bible_translation' => 'ESV',
            'user_text' => 'For God so loved the world...',
        ]);

        $response->assertStatus(200);
        
        // Check that user is now verified
        $user->refresh();
        $this->assertTrue($user->verified_user);
    }

    public function test_user_is_verified_when_updating_existing_verse()
    {
        $user = User::factory()->create(['verified_user' => false]);
        
        // Create an existing memory bank entry
        MemoryBank::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => json_encode([16]),
            'difficulty' => 'easy', // Use valid enum value
            'bible_translation' => 'ESV',
        ]);

        $this->actingAs($user);

        // Update the same verse
        $response = $this->postJson('/memorization-tool/save', [
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16],
            'difficulty' => 'easy', // Use valid enum value
            'accuracy_score' => 95.0,
            'bible_translation' => 'ESV',
            'user_text' => 'For God so loved the world that he gave...',
        ]);

        $response->assertStatus(200);
        
        // Check that user is now verified
        $user->refresh();
        $this->assertTrue($user->verified_user);
    }

    public function test_user_is_verified_when_email_is_verified()
    {
        $user = User::factory()->create(['verified_user' => false]);
        
        // Manually call the verification method since we're testing the logic
        $user->markAsVerified();
        
        $user->refresh();
        $this->assertTrue($user->verified_user);
    }

    public function test_already_verified_user_stays_verified()
    {
        $user = User::factory()->create(['verified_user' => true]);
        
        // Call markAsVerified again
        $user->markAsVerified();
        
        $this->assertTrue($user->verified_user);
    }

    public function test_super_admin_only_shows_verified_users()
    {
        // Create some verified and unverified users
        $verifiedUser = User::factory()->create(['verified_user' => true]);
        $unverifiedUser = User::factory()->create(['verified_user' => false]);
        
        // Create admin user (avoid duplicate by using firstOrCreate)
        $adminUser = User::firstOrCreate(
            ['email' => 'rileygweb@gmail.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'verified_user' => true]
        );
        $this->actingAs($adminUser);

        $response = $this->getJson('/super-admin/users');
        
        $response->assertStatus(200);
        
        $users = $response->json('users');
        $userIds = array_column($users, 'id');
        
        // Should include verified user
        $this->assertContains($verifiedUser->id, $userIds);
        
        // Should NOT include unverified user
        $this->assertNotContains($unverifiedUser->id, $userIds);
    }
}
