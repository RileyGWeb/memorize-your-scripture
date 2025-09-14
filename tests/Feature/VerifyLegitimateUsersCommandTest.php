<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class VerifyLegitimateUsersCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shows_error_when_no_options_provided()
    {
        $result = Artisan::call('users:verify-legitimate');
        
        $this->assertEquals(1, $result);
        $this->assertStringContainsString(
            'Please specify --auto or --email options', 
            Artisan::output()
        );
    }

    /** @test */
    public function it_automatically_verifies_users_with_memorized_verses()
    {
        // Create users: one with memorized verses, one without
        $userWithMemory = User::factory()->create(['verified_user' => false]);
        $userWithoutMemory = User::factory()->create(['verified_user' => false]);
        
        // Create memory bank entry for first user
        MemoryBank::factory()->create([
            'user_id' => $userWithMemory->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16]
        ]);

        $result = Artisan::call('users:verify-legitimate', ['--auto' => true]);
        
        $this->assertEquals(0, $result);
        
        // Refresh models from database
        $userWithMemory->refresh();
        $userWithoutMemory->refresh();
        
        $this->assertTrue($userWithMemory->verified_user);
        $this->assertFalse($userWithoutMemory->verified_user);
    }

    /** @test */
    public function it_automatically_verifies_users_with_verified_emails()
    {
        // Create users: one with verified email, one without
        $userWithVerifiedEmail = User::factory()->create([
            'verified_user' => false,
            'email_verified_at' => now()
        ]);
        $userWithoutVerifiedEmail = User::factory()->create([
            'verified_user' => false,
            'email_verified_at' => null
        ]);

        $result = Artisan::call('users:verify-legitimate', ['--auto' => true]);
        
        $this->assertEquals(0, $result);
        
        // Refresh models from database
        $userWithVerifiedEmail->refresh();
        $userWithoutVerifiedEmail->refresh();
        
        $this->assertTrue($userWithVerifiedEmail->verified_user);
        $this->assertFalse($userWithoutVerifiedEmail->verified_user);
    }

    /** @test */
    public function it_verifies_specific_users_by_email()
    {
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'verified_user' => false
        ]);
        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
            'verified_user' => false
        ]);
        $user3 = User::factory()->create([
            'email' => 'user3@example.com',
            'verified_user' => false
        ]);

        $result = Artisan::call('users:verify-legitimate', [
            '--email' => ['user1@example.com', 'user2@example.com']
        ]);
        
        $this->assertEquals(0, $result);
        
        // Refresh models from database
        $user1->refresh();
        $user2->refresh();
        $user3->refresh();
        
        $this->assertTrue($user1->verified_user);
        $this->assertTrue($user2->verified_user);
        $this->assertFalse($user3->verified_user);
    }

    /** @test */
    public function it_handles_nonexistent_email_addresses()
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'verified_user' => false
        ]);

        $result = Artisan::call('users:verify-legitimate', [
            '--email' => ['existing@example.com', 'nonexistent@example.com']
        ]);
        
        $this->assertEquals(0, $result);
        
        $user->refresh();
        $this->assertTrue($user->verified_user);
        
        // Should handle gracefully without errors
        $output = Artisan::output();
        $this->assertStringContainsString('existing@example.com', $output);
    }

    /** @test */
    public function it_performs_dry_run_without_making_changes()
    {
        $userWithMemory = User::factory()->create(['verified_user' => false]);
        
        MemoryBank::factory()->create([
            'user_id' => $userWithMemory->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [16]
        ]);

        $result = Artisan::call('users:verify-legitimate', [
            '--auto' => true,
            '--dry-run' => true
        ]);
        
        $this->assertEquals(0, $result);
        
        // User should not be verified in dry-run mode
        $userWithMemory->refresh();
        $this->assertFalse($userWithMemory->verified_user);
        
        // Output should indicate what would be done
        $output = Artisan::output();
        $this->assertStringContainsString('would', strtolower($output));
    }

    /** @test */
    public function it_can_combine_auto_and_email_options()
    {
        // User with memory bank (should be auto-verified)
        $userWithMemory = User::factory()->create(['verified_user' => false]);
        MemoryBank::factory()->create(['user_id' => $userWithMemory->id]);
        
        // User specified by email
        $userByEmail = User::factory()->create([
            'email' => 'specific@example.com',
            'verified_user' => false
        ]);
        
        // User that shouldn't be verified
        $untouchedUser = User::factory()->create(['verified_user' => false]);

        $result = Artisan::call('users:verify-legitimate', [
            '--auto' => true,
            '--email' => ['specific@example.com']
        ]);
        
        $this->assertEquals(0, $result);
        
        $userWithMemory->refresh();
        $userByEmail->refresh();
        $untouchedUser->refresh();
        
        $this->assertTrue($userWithMemory->verified_user);
        $this->assertTrue($userByEmail->verified_user);
        $this->assertFalse($untouchedUser->verified_user);
    }

    /** @test */
    public function it_does_not_re_verify_already_verified_users()
    {
        $alreadyVerified = User::factory()->create(['verified_user' => true]);
        $notVerified = User::factory()->create(['verified_user' => false]);
        
        MemoryBank::factory()->create(['user_id' => $alreadyVerified->id]);
        MemoryBank::factory()->create(['user_id' => $notVerified->id]);

        $result = Artisan::call('users:verify-legitimate', ['--auto' => true]);
        
        $this->assertEquals(0, $result);
        
        $alreadyVerified->refresh();
        $notVerified->refresh();
        
        // Both should be verified, but already verified user should remain unchanged
        $this->assertTrue($alreadyVerified->verified_user);
        $this->assertTrue($notVerified->verified_user);
    }

    /** @test */
    public function it_provides_helpful_output_about_verification_actions()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'verified_user' => false
        ]);
        
        MemoryBank::factory()->create(['user_id' => $user->id]);

        Artisan::call('users:verify-legitimate', ['--auto' => true]);
        
        $output = Artisan::output();
        
        // Should provide informative output about what was done
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('test@example.com', $output);
    }

    /** @test */
    public function it_handles_users_with_both_memory_and_verified_email()
    {
        $user = User::factory()->create([
            'verified_user' => false,
            'email_verified_at' => now()
        ]);
        
        MemoryBank::factory()->create(['user_id' => $user->id]);

        $result = Artisan::call('users:verify-legitimate', ['--auto' => true]);
        
        $this->assertEquals(0, $result);
        
        $user->refresh();
        $this->assertTrue($user->verified_user);
    }
}
