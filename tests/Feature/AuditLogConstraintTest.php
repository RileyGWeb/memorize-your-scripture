<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\MemoryBank;
use App\Models\AuditLog;

class AuditLogConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_handles_foreign_key_constraint_violations(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $memoryBank = MemoryBank::factory()->create([
            'user_id' => $user->id,
        ]);

        // Clear existing audit logs
        AuditLog::truncate();
        
        // Delete the user (which should trigger audit logs for the user deletion)
        $userId = $user->id;
        $user->delete();

        // Should have audit logs even though there were foreign key constraints
        $auditLogs = AuditLog::where('table_name', 'users')
            ->where('record_id', $userId)
            ->get();
            
        $this->assertGreaterThan(0, $auditLogs->count());
        
        // The user_id in audit logs should be null due to the constraint handling
        foreach ($auditLogs as $auditLog) {
            $this->assertNull($auditLog->user_id);
        }
    }
}
