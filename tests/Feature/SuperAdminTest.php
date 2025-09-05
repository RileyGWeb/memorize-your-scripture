<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_page_accessible_to_authorized_user(): void
    {
        $user = User::factory()->create([
            'id' => 1,
            'email' => 'rileygweb@gmail.com'
        ]);
        $this->actingAs($user);
        
        $response = $this->get('/super-admin');
        
        $response->assertStatus(200);
        $response->assertSee('Super Admin Panel');
    }

    public function test_super_admin_page_redirects_unauthorized_user_id(): void
    {
        $user = User::factory()->create([
            'id' => 2,
            'email' => 'rileygweb@gmail.com'
        ]);
        $this->actingAs($user);
        
        $response = $this->get('/super-admin');
        
        $response->assertRedirect('/');
    }

    public function test_super_admin_page_redirects_unauthorized_email(): void
    {
        $user = User::factory()->create([
            'id' => 1,
            'email' => 'wrong@email.com'
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

    public function test_super_admin_page_has_statistics_tab(): void
    {
        $user = User::factory()->create([
            'id' => 1,
            'email' => 'rileygweb@gmail.com'
        ]);
        $this->actingAs($user);
        
        $response = $this->get('/super-admin');
        
        $response->assertSee('Statistics');
    }

    public function test_super_admin_page_has_audit_log_tab(): void
    {
        $user = User::factory()->create([
            'id' => 1,
            'email' => 'rileygweb@gmail.com'
        ]);
        $this->actingAs($user);
        
        $response = $this->get('/super-admin');
        
        $response->assertSee('Audit Log');
    }

    public function test_super_admin_statistics_show_user_count(): void
    {
        // Create some test data
        User::factory()->count(5)->create();
        $adminUser = User::factory()->create([
            'id' => 1,
            'email' => 'rileygweb@gmail.com'
        ]);
        $this->actingAs($adminUser);
        
        $response = $this->get('/super-admin');
        
        $response->assertSee('Total Users');
        $response->assertSee('6'); // 5 created + 1 admin
    }
}
