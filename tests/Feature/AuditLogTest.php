<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use App\Models\MemorizeLater;
use App\Models\AuditLog;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_records_model_creation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [[16, 16]]
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'CREATE',
            'table_name' => 'memory_bank',
            'record_id' => $memoryBank->id,
        ]);

        $auditLog = AuditLog::where('table_name', 'memory_bank')
            ->where('record_id', $memoryBank->id)
            ->where('action', 'CREATE')
            ->first();
            
        $this->assertNotNull($auditLog);
        $this->assertNull($auditLog->old_values);
        $this->assertNotNull($auditLog->new_values);
        // Check that some key fields are captured
        $this->assertArrayHasKey('book', $auditLog->new_values);
        $this->assertArrayHasKey('chapter', $auditLog->new_values);
        $this->assertArrayHasKey('user_id', $auditLog->new_values);
        $this->assertEquals($memoryBank->book, $auditLog->new_values['book']);
        $this->assertEquals($memoryBank->chapter, $auditLog->new_values['chapter']);
    }

    public function test_audit_log_records_model_update(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [[16, 16]]
        ]);

        // Clear the creation audit log
        AuditLog::truncate();

        $memoryBank->update([
            'book' => 'Romans',
            'chapter' => 3,
            'verses' => [[23, 23]]
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'UPDATE',
            'table_name' => 'memory_bank',
            'record_id' => $memoryBank->id,
        ]);

        $auditLog = AuditLog::where('record_id', $memoryBank->id)->first();
        $this->assertEquals('John', $auditLog->old_values['book']);
        $this->assertEquals('Romans', $auditLog->new_values['book']);
    }

    public function test_audit_log_records_model_deletion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
            'verses' => [[16, 16]]
        ]);

        // Clear the creation audit log
        AuditLog::truncate();

        $memoryBankId = $memoryBank->id;
        $memoryBank->delete();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'DELETE',
            'table_name' => 'memory_bank',
            'record_id' => $memoryBankId,
        ]);

        $auditLog = AuditLog::where('record_id', $memoryBankId)->first();
        $this->assertNotNull($auditLog->old_values);
        $this->assertNull($auditLog->new_values);
        $this->assertEquals('John', $auditLog->old_values['book']);
    }

    public function test_audit_log_works_without_authenticated_user(): void
    {
        // Create a user but don't authenticate
        $user = User::factory()->create();
        
        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id, // Still need a valid user_id for foreign key
            'book' => 'John',
            'chapter' => 3,
            'verses' => [[16, 16]]
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null, // Should be null because no one is authenticated
            'action' => 'CREATE',
            'table_name' => 'memory_bank',
            'record_id' => $memoryBank->id,
        ]);
    }

    public function test_audit_log_stores_timestamps_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id,
            'book' => 'John',
            'chapter' => 3,
        ]);

        $auditLog = AuditLog::where('record_id', $memoryBank->id)->first();
        $this->assertNotNull($auditLog->performed_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $auditLog->performed_at);
    }

    public function test_audit_log_has_user_relationship(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id,
        ]);

        $auditLog = AuditLog::where('table_name', 'memory_bank')
            ->where('record_id', $memoryBank->id)
            ->where('action', 'CREATE')
            ->first();
            
        $this->assertNotNull($auditLog);
        $this->assertNotNull($auditLog->user);
        $this->assertEquals($user->id, $auditLog->user->id);
        $this->assertEquals($user->email, $auditLog->user->email);
    }

    public function test_multiple_model_types_can_be_audited(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create different types of models
        $memoryBank = MemoryBank::factory()->create(['user_id' => $user->id]);
        $memorizeLater = MemorizeLater::factory()->create(['user_id' => $user->id]);

        // Should have audit logs for both
        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'memory_bank',
            'record_id' => $memoryBank->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'memorize_later',
            'record_id' => $memorizeLater->id,
        ]);
    }
}
