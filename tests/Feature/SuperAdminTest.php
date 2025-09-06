<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser()
    {
        return User::firstOrCreate(
            ['email' => 'rileygweb@gmail.com'],
            ['name' => 'Riley Goodman']
        );
    }

    public function test_super_admin_page_accessible_to_authorized_user(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);
        
        $response = $this->get('/super-admin');
        
        $response->assertStatus(200);
        $response->assertSee('Super Admin Panel');
    }

    public function test_super_admin_page_redirects_unauthorized_user_email(): void
    {
        $user = User::factory()->create([
            'email' => 'wrong@email.com'
        ]);
        $this->actingAs($user);
        
        $response = $this->get('/super-admin');
        
        $response->assertRedirect('/');
    }

    public function test_super_admin_page_redirects_unauthorized_email(): void
    {
        $user = User::factory()->create([
            'email' => 'another-wrong@email.com'
        ]);
        $this->actingAs($user);
        
        $response = $this->get('/super-admin');
        
        $response->assertRedirect('/');
    }

    public function test_super_admin_page_redirects_guest_user(): void
    {
        $response = $this->get('/super-admin');
        
        $response->assertRedirect('/');
    }

    public function test_super_admin_page_has_dashboard_tab(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);
        
        $response = $this->get('/super-admin');
        
        $response->assertSee('Dashboard');
    }

    public function test_super_admin_page_has_audit_log_tab(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);
        
        $response = $this->get('/super-admin');
        
        $response->assertSee('Audit Log');
    }

    public function test_super_admin_statistics_show_user_count(): void
    {
        // Create some test data
        User::factory()->count(5)->create();
        $adminUser = $this->createAdminUser();
        $this->actingAs($adminUser);
        
        $response = $this->get('/super-admin');
        
        $response->assertSee('Total Users');
        $response->assertSee('6'); // 5 created + 1 admin
    }

    public function test_super_admin_users_endpoint_accessible_to_authorized_user(): void
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);
        
        $response = $this->get('/super-admin/users');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'users',
            'total'
        ]);
    }

    public function test_super_admin_users_endpoint_redirects_unauthorized_user(): void
    {
        $user = User::factory()->create([
            'email' => 'unauthorized@email.com'
        ]);
        $this->actingAs($user);
        
        $response = $this->get('/super-admin/users');
        
        $response->assertStatus(403);
    }
}
