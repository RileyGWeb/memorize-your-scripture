<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PWATest extends TestCase
{
    public function test_offline_page_loads()
    {
        $response = $this->get('/offline');
        
        $response->assertStatus(200);
        $response->assertSee("You're offline", false); // Use false to avoid HTML encoding
        $response->assertSee('This page is always available without internet', false);
    }

    public function test_main_layout_has_pwa_meta_tags()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false);
        $response->assertSee('<meta name="theme-color" content="#111827">', false);
        $response->assertSee('<meta name="mobile-web-app-capable" content="yes">', false);
        $response->assertSee('<link rel="apple-touch-icon" href="/apple-touch-icon.png">', false);
    }

    public function test_pwa_icons_exist()
    {
        $this->assertFileExists(public_path('apple-touch-icon.png'));
        $this->assertFileExists(public_path('icons/pwa-192x192.jpg'));
        $this->assertFileExists(public_path('icons/pwa-512x512.jpg'));
        $this->assertFileExists(public_path('icons/maskable-512.jpg'));
    }

    public function test_vite_config_includes_pwa_setup()
    {
        $viteConfig = file_get_contents(base_path('vite.config.js'));
        
        $this->assertStringContainsString('vite-plugin-pwa', $viteConfig);
        $this->assertStringContainsString('VitePWA', $viteConfig);
        $this->assertStringContainsString('registerType: \'autoUpdate\'', $viteConfig);
        $this->assertStringContainsString('navigateFallback: \'/offline\'', $viteConfig);
    }

    public function test_pwa_js_module_exists()
    {
        $this->assertFileExists(resource_path('js/pwa.js'));
        
        $pwaContent = file_get_contents(resource_path('js/pwa.js'));
        $this->assertStringContainsString('virtual:pwa-register', $pwaContent);
        $this->assertStringContainsString('sw:need-refresh', $pwaContent);
        $this->assertStringContainsString('sw:offline-ready', $pwaContent);
    }

    public function test_app_js_imports_pwa_module()
    {
        $appJsContent = file_get_contents(resource_path('js/app.js'));
        $this->assertStringContainsString('./pwa', $appJsContent);
    }

    public function test_caching_rules_exclude_livewire_endpoints()
    {
        $viteConfig = file_get_contents(base_path('vite.config.js'));
        
        // Check that Livewire endpoints are excluded from caching
        $this->assertStringContainsString('\/livewire\/', $viteConfig); // Regex pattern
        $this->assertStringContainsString('NetworkOnly', $viteConfig);
        $this->assertStringContainsString('(login|logout|register)', $viteConfig); // Auth endpoints group
        $this->assertStringContainsString('\/password\/', $viteConfig);
        $this->assertStringContainsString('\/sanctum\/csrf-cookie', $viteConfig);
    }

    public function test_manifest_configuration_is_correct()
    {
        $viteConfig = file_get_contents(base_path('vite.config.js'));
        
        $this->assertStringContainsString('Memorize Your Scripture', $viteConfig);
        $this->assertStringContainsString('Scripture Memory', $viteConfig);
        $this->assertStringContainsString('display: \'standalone\'', $viteConfig);
        $this->assertStringContainsString('start_url: \'/\'', $viteConfig);
    }
}
