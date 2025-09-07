<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class InstallPromptTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function install_prompt_component_is_present_on_authenticated_pages()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSeeLivewire('install-prompt');
    }

    /** @test */
    public function install_prompt_component_is_present_on_guest_pages()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSeeLivewire('install-prompt');
    }

    /** @test */
    public function install_prompt_contains_install_button()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('install-button', false);
        $response->assertSee('Install Scripture App');
    }

    /** @test */
    public function install_prompt_contains_floating_button()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('floating-install-button', false);
    }

    /** @test */
    public function install_prompt_has_proper_styling_classes()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('bg-indigo-600');
        $response->assertSee('fixed bottom-4');
        $response->assertSee('rounded-lg shadow-lg');
    }

    /** @test */
    public function install_prompt_includes_ios_detection_script()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('beforeinstallprompt', false);
        $response->assertSee('isiOS()', false);
        $response->assertSee('Add to Home Screen', false);
    }
}
