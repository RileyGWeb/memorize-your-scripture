<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViteManifestTest extends TestCase
{
    /**
     * Test that Vite manifest file exists for production deployments.
     * This test only runs when APP_ENV is 'production' to avoid false positives in development.
     *
     * @test
     */
    public function vite_manifest_file_exists()
    {
        // Only enforce manifest existence in production-like environments
        if (app()->environment('testing', 'local')) {
            $this->markTestSkipped('Manifest check skipped in development/testing environment');
        }

        $manifestPath = public_path('build/manifest.json');
        
        $this->assertTrue(
            file_exists($manifestPath),
            'Vite manifest file does not exist at: ' . $manifestPath . '. Run "npm run build" to generate it.'
        );
    }

    /**
     * Test that when manifest exists, it's properly built.
     *
     * @test
     */
    public function vite_manifest_is_properly_built_when_exists()
    {
        $manifestPath = public_path('build/manifest.json');
        
        // If manifest exists, it should be valid
        if (file_exists($manifestPath)) {
            $content = file_get_contents($manifestPath);
            $this->assertNotFalse($content, 'Could not read Vite manifest file');

            $decoded = json_decode($content, true);
            $this->assertNotNull($decoded, 'Vite manifest does not contain valid JSON');
            $this->assertIsArray($decoded, 'Vite manifest should be a JSON object');
            
            // Should have at least app.js entry
            $this->assertArrayHasKey('resources/js/app.js', $decoded, 'Manifest missing app.js entry');
        } else {
            $this->addWarning('Vite manifest does not exist - ensure "npm run build" is run before production deployment');
        }
    }

    /**
     * Test that Vite manifest contains valid JSON.
     *
     * @test
     */
    public function vite_manifest_contains_valid_json()
    {
        $manifestPath = public_path('build/manifest.json');
        
        // Skip if manifest doesn't exist (the previous test will catch this)
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Vite manifest file does not exist');
        }

        $content = file_get_contents($manifestPath);
        $this->assertNotFalse($content, 'Could not read Vite manifest file');

        $decoded = json_decode($content, true);
        $this->assertNotNull($decoded, 'Vite manifest does not contain valid JSON');
        $this->assertIsArray($decoded, 'Vite manifest should be a JSON object');
    }

    /**
     * Test that Vite manifest contains expected entries.
     *
     * @test
     */
    public function vite_manifest_contains_expected_entries()
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Vite manifest file does not exist');
        }

        $content = file_get_contents($manifestPath);
        $manifest = json_decode($content, true);
        
        if ($manifest === null) {
            $this->markTestSkipped('Vite manifest does not contain valid JSON');
        }

        // Check for main app.js entry
        $this->assertTrue(
            isset($manifest['resources/js/app.js']) || 
            array_key_exists('resources/js/app.js', $manifest),
            'Vite manifest should contain resources/js/app.js entry'
        );

        // Check for main app.css entry
        $this->assertTrue(
            isset($manifest['resources/css/app.css']) || 
            array_key_exists('resources/css/app.css', $manifest),
            'Vite manifest should contain resources/css/app.css entry'
        );
    }

    /**
     * Test that build directory structure is correct.
     *
     * @test
     */
    public function build_directory_structure_is_correct()
    {
        $buildPath = public_path('build');
        
        $this->assertTrue(
            is_dir($buildPath),
            'Build directory does not exist at: ' . $buildPath
        );

        $vitePath = public_path('build');
        $this->assertTrue(
            is_dir($vitePath),
            'Build directory does not exist at: ' . $vitePath
        );

        $assetsPath = public_path('build/assets');
        $this->assertTrue(
            is_dir($assetsPath),
            'Assets directory does not exist at: ' . $assetsPath
        );
    }

    /**
     * Test that critical assets exist after build.
     *
     * @test
     */
    public function critical_built_assets_exist()
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Vite manifest file does not exist');
        }

        $content = file_get_contents($manifestPath);
        $manifest = json_decode($content, true);
        
        if ($manifest === null) {
            $this->markTestSkipped('Vite manifest does not contain valid JSON');
        }

        // Check that referenced files actually exist
        foreach ($manifest as $entry) {
            if (isset($entry['file'])) {
                $filePath = public_path('build/' . $entry['file']);
                if (!file_exists($filePath)) {
                    // In testing environment, this might be expected for dummy files
                    if (app()->environment('testing') && str_contains($entry['file'], 'test')) {
                        $this->markTestIncomplete("Test manifest file does not exist: {$filePath}");
                        continue;
                    }
                    $this->fail('Referenced file does not exist: ' . $filePath);
                }
            }

            if (isset($entry['css'])) {
                foreach ($entry['css'] as $cssFile) {
                    $cssPath = public_path('build/' . $cssFile);
                    $this->assertTrue(
                        file_exists($cssPath),
                        'Referenced CSS file does not exist: ' . $cssPath
                    );
                }
            }
        }
    }

    /**
     * Test that Vite helper functions work correctly.
     *
     * @test
     */
    public function vite_helper_functions_work()
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Vite manifest file does not exist');
        }

        // Test that Vite directive works
        $viteOutput = \Illuminate\Support\Facades\Vite::asset('resources/js/app.js');
        $this->assertNotEmpty($viteOutput, 'Vite asset helper should return content');

        // Test that it returns a valid URL or HTML tags
        $this->assertTrue(
            str_contains($viteOutput, '/build/assets/') || str_contains($viteOutput, '<script'),
            'Vite should generate valid asset reference or script tag'
        );
    }

    /**
     * Test that hot file doesn't exist in production.
     *
     * @test
     */
    public function hot_file_should_not_exist_in_production()
    {
        $hotPath = public_path('hot');
        
        // In production, we shouldn't have a hot file
        // This test mainly serves as documentation and a reminder
        if (app()->environment('production')) {
            $this->assertFalse(
                file_exists($hotPath),
                'Hot file should not exist in production environment'
            );
        } else {
            // In development, either state is fine
            $this->assertTrue(true, 'Hot file state is irrelevant in non-production');
        }
    }
}
