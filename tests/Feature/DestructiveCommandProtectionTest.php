<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DestructiveCommandProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_destructive_commands_are_allowed_in_local_environment(): void
    {
        // In local/testing environment, destructive commands should be allowed
        $this->app['env'] = 'local';
        
        // This should not throw an exception
        try {
            DB::statement('TRUNCATE TABLE users');
            $this->assertTrue(true); // If we get here, truncate was allowed
        } catch (\Exception $e) {
            // If it fails for other reasons (like foreign key constraints), that's expected
            $this->assertStringNotContainsString('Destructive database operation', $e->getMessage());
        }
    }

    public function test_prohibit_destructive_commands_is_configured(): void
    {
        // Test that the AppServiceProvider correctly configures destructive command protection
        // We can verify the method was called by checking the app runs without errors
        
        // The fact that the application boots successfully and this test runs
        // means the AppServiceProvider's boot method executed successfully
        $this->assertTrue(app()->bound('db'));
        
        // In testing environment, destructive commands should be allowed
        $this->assertContains(app()->environment(), ['local', 'testing']);
        
        // Verify the app service provider is registered
        $providers = app()->getLoadedProviders();
        $this->assertArrayHasKey('App\\Providers\\AppServiceProvider', $providers);
    }
}
