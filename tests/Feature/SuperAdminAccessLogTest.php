<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AuditLog;

class SuperAdminAccessLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_access_is_logged(): void
    {
        // Clear any existing audit logs
        AuditLog::query()->delete();

        $user = User::factory()->create([
            'email' => 'regular@example.com',
            'name' => 'Regular User',
        ]);

        $this->actingAs($user);

        $response = $this->get('/super-admin');

        // Should redirect unauthorized users
        $response->assertRedirect('/');

        // Check that access attempt was logged
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'SUPER_ADMIN_ACCESS_DENIED',
            'table_name' => 'super_admin',
        ]);

        $auditLog = AuditLog::where('user_id', $user->id)->first();
        $this->assertNotNull($auditLog);
        
        $newValues = $auditLog->new_values;
        $this->assertEquals('index', $newValues['endpoint']);
        $this->assertStringContainsString('Access denied: User email \'regular@example.com\' is not authorized', $newValues['reason']);
        $this->assertEquals('regular@example.com', $newValues['authenticated_user_email']);
    }

    public function test_authorized_user_access_is_logged(): void
    {
        // Clear any existing audit logs
        AuditLog::query()->delete();

        $user = User::firstOrCreate(
            ['email' => 'rileygweb@gmail.com'],
            [
                'name' => 'Riley Admin',
                'password' => bcrypt('password'),
            ]
        );

        $this->actingAs($user);

        $response = $this->get('/super-admin');

        // Should allow access
        $response->assertOk();
        $response->assertViewIs('super-admin.index');

        // Check that access attempt was logged
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'SUPER_ADMIN_ACCESS_GRANTED',
            'table_name' => 'super_admin',
        ]);

        $auditLog = AuditLog::where('user_id', $user->id)->first();
        $this->assertNotNull($auditLog);
        
        $newValues = $auditLog->new_values;
        $this->assertEquals('index', $newValues['endpoint']);
        $this->assertStringContainsString('Access granted: User authorized as super admin', $newValues['reason']);
        $this->assertEquals('rileygweb@gmail.com', $newValues['authenticated_user_email']);
    }

    public function test_unauthenticated_access_is_logged(): void
    {
        // Clear any existing audit logs
        AuditLog::query()->delete();

        $response = $this->get('/super-admin');

        // Should redirect unauthenticated users
        $response->assertRedirect('/');

        // Check that access attempt was logged
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'action' => 'SUPER_ADMIN_ACCESS_DENIED',
            'table_name' => 'super_admin',
        ]);

        $auditLog = AuditLog::whereNull('user_id')->first();
        $this->assertNotNull($auditLog);
        
        $newValues = $auditLog->new_values;
        $this->assertEquals('index', $newValues['endpoint']);
        $this->assertStringContainsString('Access denied: User not authenticated', $newValues['reason']);
        $this->assertNull($newValues['authenticated_user_email']);
    }

    public function test_api_endpoint_access_is_logged(): void
    {
        // Clear any existing audit logs
        AuditLog::query()->delete();

        $user = User::firstOrCreate(
            ['email' => 'rileygweb@gmail.com'],
            [
                'name' => 'Riley Admin',
                'password' => bcrypt('password'),
            ]
        );

        $this->actingAs($user);

        $response = $this->get('/super-admin/users');

        // Should allow access
        $response->assertOk();

        // Check that access attempt was logged with correct endpoint
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'SUPER_ADMIN_ACCESS_GRANTED',
            'table_name' => 'super_admin',
        ]);

        $auditLog = AuditLog::where('user_id', $user->id)->first();
        $this->assertNotNull($auditLog);
        
        $newValues = $auditLog->new_values;
        $this->assertEquals('users-api', $newValues['endpoint']);
    }

    public function test_audit_log_includes_ip_and_user_agent(): void
    {
        // Clear any existing audit logs
        AuditLog::query()->delete();

        $user = User::firstOrCreate(
            ['email' => 'rileygweb@gmail.com'],
            [
                'name' => 'Riley Admin',
                'password' => bcrypt('password'),
            ]
        );

        $this->actingAs($user);

        $response = $this->withHeaders([
            'HTTP_USER_AGENT' => 'Test Browser 1.0',
        ])->get('/super-admin');

        // Should allow access
        $response->assertOk();

        $auditLog = AuditLog::where('user_id', $user->id)->first();
        $this->assertNotNull($auditLog);
        
        $newValues = $auditLog->new_values;
        $this->assertArrayHasKey('ip_address', $newValues);
        $this->assertArrayHasKey('user_agent', $newValues);
        $this->assertEquals('Test Browser 1.0', $newValues['user_agent']);
    }
}
