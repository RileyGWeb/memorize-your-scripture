<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AuditLog;
use Livewire\Livewire;
use App\Livewire\AuditLogTable;

class AuditLogTableTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'rileygweb@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
    }

    public function test_audit_log_table_renders_for_authorized_user(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        // Create some audit logs
        AuditLog::factory()->count(5)->create();

        Livewire::test(AuditLogTable::class)
            ->assertStatus(200)
            ->assertSee('Search users, actions, tables')
            ->assertSee('All Actions')
            ->assertSee('All Tables');
    }

    public function test_audit_log_table_displays_audit_logs(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        // Create specific audit logs
        $log1 = AuditLog::factory()->create([
            'action' => 'CREATE',
            'table_name' => 'users',
            'user_id' => $user->id,
        ]);

        $log2 = AuditLog::factory()->create([
            'action' => 'UPDATE',
            'table_name' => 'memory_bank',
            'user_id' => $user->id,
        ]);

        Livewire::test(AuditLogTable::class)
            ->assertSee('Create')
            ->assertSee('Update')
            ->assertSee('users')
            ->assertSee('memory_bank')
            ->assertSee($user->name);
    }

    public function test_search_functionality(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        // Clear any existing audit logs 
        AuditLog::query()->delete();

        // Create audit logs with specific data (don't use factory for user to avoid extra logs)
        AuditLog::create([
            'action' => 'CREATE',
            'table_name' => 'users',
            'user_id' => $user->id,
            'record_id' => 1,
            'performed_at' => now(),
        ]);

        AuditLog::create([
            'action' => 'DELETE',
            'table_name' => 'memory_bank',
            'user_id' => $user->id,
            'record_id' => 2,
            'performed_at' => now(),
        ]);

        $component = Livewire::test(AuditLogTable::class)
            ->set('search', 'users');

        // Debug: Check total audit logs and filtered results
        $totalLogs = AuditLog::count();
        $filteredLogs = $component->viewData('auditLogs');
        
        $this->assertEquals(2, $totalLogs, "Should have exactly 2 audit logs in database");
        $component->assertSee('users');
        
        // Check that only 1 result is shown when searching for 'users'
        $this->assertEquals(1, $filteredLogs->count(), "Search should return 1 result, got: " . $filteredLogs->count());
    }

    public function test_action_filter(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        // Clear any existing audit logs 
        AuditLog::query()->delete();

        AuditLog::create([
            'action' => 'CREATE',
            'table_name' => 'users',
            'user_id' => $user->id,
            'record_id' => 1,
            'performed_at' => now(),
        ]);

        AuditLog::create([
            'action' => 'DELETE',
            'table_name' => 'memory_bank',
            'user_id' => $user->id,
            'record_id' => 2,
            'performed_at' => now(),
        ]);

        $component = Livewire::test(AuditLogTable::class)
            ->set('actionFilter', 'CREATE');

        $component->assertSee('Create');
        
        // Check that only 1 result is shown when filtering by CREATE
        $this->assertEquals(1, $component->viewData('auditLogs')->count());
    }

    public function test_table_filter(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        // Clear any existing audit logs 
        AuditLog::query()->delete();

        AuditLog::create([
            'action' => 'CREATE',
            'table_name' => 'users',
            'user_id' => $user->id,
            'record_id' => 1,
            'performed_at' => now(),
        ]);

        AuditLog::create([
            'action' => 'CREATE',
            'table_name' => 'memory_bank',
            'user_id' => $user->id,
            'record_id' => 2,
            'performed_at' => now(),
        ]);

        $component = Livewire::test(AuditLogTable::class)
            ->set('tableFilter', 'users');

        $component->assertSee('users');
        
        // Check that only 1 result is shown when filtering by users table
        $this->assertEquals(1, $component->viewData('auditLogs')->count());
    }

    public function test_sorting_functionality(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        // Create logs with different dates
        $oldLog = AuditLog::factory()->create([
            'action' => 'CREATE',
            'table_name' => 'users',
            'user_id' => $user->id,
            'created_at' => now()->subDays(2),
        ]);

        $newLog = AuditLog::factory()->create([
            'action' => 'UPDATE',
            'table_name' => 'memory_bank',
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        // Test sorting by created_at ascending
        Livewire::test(AuditLogTable::class)
            ->call('sortBy', 'created_at')
            ->assertSet('sortDirection', 'asc');
    }

    public function test_pagination(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        // Clear any existing audit logs 
        AuditLog::query()->delete();

        // Create exactly 20 logs 
        for ($i = 0; $i < 20; $i++) {
            AuditLog::create([
                'action' => 'CREATE',
                'table_name' => 'users',
                'user_id' => $user->id,
                'record_id' => $i + 1,
                'performed_at' => now(),
            ]);
        }

        $component = Livewire::test(AuditLogTable::class)
            ->set('perPage', 10);

        // Check that pagination is working by verifying we have exactly 10 items on the page
        $this->assertEquals(10, $component->viewData('auditLogs')->count());
        $this->assertEquals(20, $component->viewData('auditLogs')->total());
    }

    public function test_clear_filters(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        // Clear any existing audit logs 
        AuditLog::query()->delete();

        AuditLog::create([
            'action' => 'CREATE',
            'table_name' => 'users',
            'user_id' => $user->id,
            'record_id' => 1,
            'performed_at' => now(),
        ]);

        Livewire::test(AuditLogTable::class)
            ->set('search', 'test')
            ->set('actionFilter', 'CREATE')
            ->set('tableFilter', 'users')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('actionFilter', '')
            ->assertSet('tableFilter', '');
    }

    public function test_per_page_options(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        // Clear any existing audit logs 
        AuditLog::query()->delete();

        // Create exactly 30 logs
        for ($i = 0; $i < 30; $i++) {
            AuditLog::create([
                'action' => 'CREATE',
                'table_name' => 'users',
                'user_id' => $user->id,
                'record_id' => $i + 1,
                'performed_at' => now(),
            ]);
        }

        $component = Livewire::test(AuditLogTable::class)
            ->set('perPage', 25);

        // Check that we get exactly 25 items per page
        $this->assertEquals(25, $component->viewData('auditLogs')->count());
        $this->assertEquals(30, $component->viewData('auditLogs')->total());
    }

    public function test_empty_state(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        // Clear any existing audit logs to ensure empty state
        AuditLog::query()->delete();

        Livewire::test(AuditLogTable::class)
            ->assertSee('No audit logs found')
            ->assertSee('Try adjusting your search or filter criteria');
    }
}
